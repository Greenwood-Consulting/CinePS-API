<?php

namespace App\Controller;

use App\Entity\Vote;
use App\Entity\Semaine;
use App\Entity\Proposition;
use App\Repository\VoteRepository;
use JMS\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\PropositionRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class VoteController extends AbstractController
{
    #[Route('/api/votes', name: 'app_vote')]
    public function getAllVotes(VoteRepository $voteRepository, SerializerInterface $serializer): JsonResponse
    {

        $voteList = $voteRepository->findAll();

        $jsonVoteList = $serializer->serialize($voteList, 'json');
        return new JsonResponse($jsonVoteList, Response::HTTP_OK, [], true);
    }

    #[Route('/api/propositions/{id}', name: 'detailProposition', methods: ['GET'])]
    public function getDetailFilm(int $id, SerializerInterface $serializer, PropositionRepository $propostionRepository): JsonResponse
    {

        $proposition = $propostionRepository->find($id);
        if($proposition) {
            $jsonProposition = $serializer->serialize($proposition, 'json');
            return new JsonResponse($jsonProposition, Response::HTTP_OK, [], true);
        }
        return new JsonResponse(["error" => "Not Found"], 404);
    }


    //Afficher les votes de la semaine $id_semaine
    #[Route('/votes/{id_semaine}', name:'VotesSemaine', methods: ['GET'])]
    public function voteSemaine(int $id_semaine, EntityManagerInterface $entityManager, SerializerInterface $serializer): JsonResponse
    {
        //Récupérer les votes des proposeurs de $id_semaine
        $queryBuilder_get_votes = $entityManager->createQueryBuilder();
        $queryBuilder_get_votes->select('v')
        ->from(Vote::class, 'v')
        ->where('v.semaine = :semaine')
        ->setParameter('semaine', $id_semaine);

        $resultats_votes = $queryBuilder_get_votes->getQuery()->getResult();
        $jsonResultatsVotes = $serializer->serialize($resultats_votes, 'json');

        return new JsonResponse ($jsonResultatsVotes, Response::HTTP_OK, [], true);
    }

    //Afficher les film victorieux de la semaine id_semaine
    #[Route('/filmVictorieux/{id_semaine}', name:'FilmVictorieux', methods: ['GET'])]
    public function filmVictorieux(int $id_semaine, EntityManagerInterface $entityManager, SerializerInterface $serializer): JsonResponse
    {
        //Récupérer le film de la semaine qui a le score le plus élevé
        $queryBuilder_get_film_victorieux = $entityManager->createQueryBuilder();
        $queryBuilder_get_film_victorieux->select('p')
        ->from(Proposition::class, 'p')
        ->orderBy('p.score', 'DESC')
        ->setMaxResults(1)
        ->where('p.semaine = :semaine')
        ->setParameter('semaine', $id_semaine);

        $film_victorieux = $queryBuilder_get_film_victorieux->getQuery()->getResult();
        $jsonFilmVictorieux = $serializer->serialize($film_victorieux, 'json');

        return new JsonResponse ($jsonFilmVictorieux, Response::HTTP_OK, [], true);
    }



}
