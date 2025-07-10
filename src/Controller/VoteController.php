<?php

namespace App\Controller;

use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Annotation\Model;
use App\Entity\Vote;
use App\Entity\AVote;
use App\Entity\Proposition;
use App\Service\CurrentSemaine;
use App\Service\FilmVictorieux;
use App\Repository\MembreRepository;
use App\Repository\SemaineRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\PropositionRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class VoteController extends AbstractController
{
    #[OA\Tag(name: 'Vote')]
    #[OA\Get(
        path: '/api/filmVictorieux/{id_semaine}',
        summary: 'Retrieve the victorious film(s) for a given week',
        description: 'Returns the film(s) with the highest votes for the specified week. Multiple films can be returned in case of a tie.',
        parameters: [
            new OA\Parameter(
                name: 'id_semaine',
                in: 'path',
                required: true,
                description: 'The ID of the week to retrieve the victorious film(s) for',
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful response with the victorious film(s)',
                content: new OA\JsonContent(ref: new Model(type: Proposition::class, groups: ['getPropositions']))
            ),
            new OA\Response(
                response: 404,
                description: 'Week not found'
            )
        ]
    )]
    // Retourner le  ou les film victorieux de la semaine id_semaine 
    //Il y a un tableau car il peut y avoir plusieurs films à égalité
    #[Route('/api/filmVictorieux/{id_semaine}', name:'FilmVictorieux', methods: ['GET'])]
    public function filmVictorieux(int $id_semaine, FilmVictorieux $filmVictorieux, SemaineRepository $semaineRepository, EntityManagerInterface $entityManager, SerializerInterface $serializer): JsonResponse
    {
        $film_victorieux = $filmVictorieux->getFilmVictorieux($id_semaine, $semaineRepository, $entityManager, $serializer);
        $jsonFilmVictorieux = $serializer->serialize($film_victorieux, 'json', ['groups' => 'getPropositions']);
        return new JsonResponse($jsonFilmVictorieux, Response::HTTP_OK, [], true);
    }

    #[OA\Tag(name: 'Vote')]
    #[OA\Post(
        path: '/api/avote/{id_membre}',
        summary: 'Register a new entry in the AVote table',
        description: 'Creates a new AVote entry for the specified member and the current week.',
        parameters: [
            new OA\Parameter(
                name: 'id_membre',
                in: 'path',
                required: true,
                description: 'The ID of the member registering the vote',
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 201,
                description: 'AVote entry successfully created',
                content: new OA\JsonContent(ref: new Model(type: AVote::class))
            ),
            new OA\Response(
                response: 404,
                description: 'Member not found'
            )
        ]
    )]
    // Enregistre une nouvelle ligne dans la table 'AVote'
    #[Route('/api/avote/{id_membre}', name:"aVote", methods: ['POST'])]
    public function avote(int $id_membre, CurrentSemaine $currentSemaine, SemaineRepository $semaineRepository, MembreRepository $membreRepository, SerializerInterface $serializer, EntityManagerInterface $em): JsonResponse 
    {
        $votant = $membreRepository->findOneById($id_membre);

        $avote = new AVote();
        $avote->setVotant($votant);
        $avote->setSemaine($currentSemaine->getCurrentSemaine($semaineRepository));

        $em->persist($avote);
        $em->flush();

        $jsonAVote = $serializer->serialize($avote, 'json', ['groups' => 'avote:read']);
        return new JsonResponse($jsonAVote, Response::HTTP_CREATED, [], true);
   }

    #[OA\Tag(name: 'Vote')]
    #[OA\Post(
        path: '/api/saveVoteProposition',
        summary: 'Save a vote and update the score of a proposition',
        description: 'Registers a vote for a proposition, updates the proposition\'s score, and creates a new Vote entry.',
        requestBody: new OA\RequestBody(
            description: 'JSON payload containing the member ID, proposition ID, and vote value',
            required: true,
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(property: 'membre', type: 'integer', description: 'The ID of the member voting'),
                    new OA\Property(property: 'proposition', type: 'integer', description: 'The ID of the proposition being voted on'),
                    new OA\Property(property: 'vote', type: 'integer', description: 'The value of the vote (positive or negative)')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Vote successfully registered',
                content: new OA\JsonContent(ref: new Model(type: Vote::class))
            ),
            new OA\Response(
                response: 404,
                description: 'Member or proposition not found'
            )
        ]
    )]
    // Enregistre le vote et met à jour le score de la proposition
    #[Route('/api/saveVoteProposition', name:"saveVoteProposition", methods: ['POST'])]
    public function saveVoteProposition(Request $request, CurrentSemaine $currentSemaine, SemaineRepository $semaineRepository, PropositionRepository $propositionRepository, MembreRepository $membreRepository, SerializerInterface $serializer, EntityManagerInterface $em): JsonResponse 
    {
        $array_request = json_decode($request->getContent(), true);
        $membre = $membreRepository->findOneById($array_request['membre']);
        $proposition = $propositionRepository->findOneById($array_request['proposition']);
        $proposition->setScore($proposition->getScore() - $array_request['vote']);

        $vote = new Vote();
        $vote->setSemaine($currentSemaine->getCurrentSemaine($semaineRepository));
        $vote->setMembre($membre);
        $vote->setProposition($proposition);
        $vote->setVote($array_request['vote']);

        $em->persist($vote);
        $em->flush();

        $jsonVote = $serializer->serialize($vote, 'json'); 
        return new JsonResponse($jsonVote, Response::HTTP_CREATED, [], true);
    }
}
