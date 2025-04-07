<?php

namespace App\Controller;

use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Annotation\Model;
use App\Entity\Semaine;
use App\Repository\MembreRepository;
use App\Repository\SemaineRepository;
use Symfony\Component\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class StatsController extends AbstractController
{
    // @TODO : merger tous ces endpoints en un seul endpoint car ils partent tous du proposeur en réalité

    #[OA\Tag(name: 'Statistics')]
    #[OA\Get(
        path: '/api/getNbPropositionsParProposeur',
        summary: 'Get the number of propositions per proposer',
        description: 'Retrieve the count of propositions grouped by proposer, excluding specific types.',
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful response',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        type: 'object',
                        properties: [
                            new OA\Property(property: 'proposeur', type: 'string', description: 'Name of the proposer'),
                            new OA\Property(property: 'nb_semaines', type: 'integer', description: 'Number of weeks')
                        ]
                    )
                )
            )
        ]
    )]
    //Récupère le nombre de proposeur 
    #[Route('/api/getNbPropositionsParProposeur', name: 'app_get_nbproposeur', methods: ['GET'])]
    public function getCountProposeurSemaine(EntityManagerInterface $entityManager, SerializerInterface $serializer): JsonResponse
    {
        $queryBuilder_get_nb_propositions_par_proposeur = $entityManager->createQueryBuilder();
        $queryBuilder_get_nb_propositions_par_proposeur->select('p.Nom AS proposeur','COUNT(s.id) AS nb_semaines')
        ->from(Semaine::class, 's')
        ->where("s.type != 'PSSansFilm' ")
        ->andWhere("s.type != 'PasDePS' ")
        ->innerJoin('s.proposeur' ,'p')
        ->groupBy('s.proposeur');
        
        $Resultat_nb_propositions_par_proposeur = $queryBuilder_get_nb_propositions_par_proposeur->getQuery()->getResult();
        $jsonResultatNbPropositionParProposeur = $serializer->serialize($Resultat_nb_propositions_par_proposeur, 'json');
        
        if(isset($jsonResultatNbPropositionParProposeur))
        return new JsonResponse ($jsonResultatNbPropositionParProposeur, Response::HTTP_OK, [], true);
    }

    #[OA\Tag(name: 'Statistics')]
    #[OA\Get(
        path: '/api/usersSatisfaction',
        summary: 'Get user satisfaction data',
        description: 'Retrieve satisfaction votes for all users, sorted by satisfaction level.',
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful response',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        type: 'object',
                        properties: [
                            new OA\Property(property: 'user', type: 'object', description: 'User details'),
                            new OA\Property(property: 'satisfactionVote', type: 'integer', description: 'Satisfaction vote of the user')
                        ]
                    )
                )
            )
        ]
    )]
    #[Route('/api/usersSatisfaction', name: 'app_users_satisfaction', methods: ['GET'])]
    public function getUsersSatisfaction(EntityManagerInterface $entityManager, MembreRepository $membreRepository, SerializerInterface $serializer): JsonResponse
    {
        $users = $membreRepository->findAll();

        $usersSatisfaction = [];
        foreach ($users as $user) {
            $satisfactionVote = $user->getSatisfactionVotes($entityManager);
            $usersSatisfaction[] = [
            'user' => $user,
            'satisfactionVote' => $satisfactionVote,
            ];
        }

        usort($usersSatisfaction, function ($a, $b) {
            return $a['satisfactionVote'] <=> $b['satisfactionVote'];
        });

        $jsonUsersSatisfaction = $serializer->serialize($usersSatisfaction, 'json');

        return new JsonResponse($jsonUsersSatisfaction, Response::HTTP_OK, [], true);
    }

    #[OA\Tag(name: 'Statistics')]
    #[OA\Get(
        path: '/api/usersNotesMoyennes',
        summary: 'Get average notes per user',
        description: 'Retrieve the average notes given by users, including the number of notes, sorted by average note.',
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful response',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        type: 'object',
                        properties: [
                            new OA\Property(property: 'user', type: 'object', description: 'User details'),
                            new OA\Property(property: 'noteMoyenne', type: 'number', format: 'float', description: 'Average note of the user'),
                            new OA\Property(property: 'nbNotes', type: 'integer', description: 'Number of notes given by the user')
                        ]
                    )
                )
            )
        ]
    )]
    #[Route('/api/usersNotesMoyennes', name: 'app_users_notes_moyennes', methods: ['GET'])]
    public function getNotesMoyennesParMembre(EntityManagerInterface $entityManager, MembreRepository $membreRepository, SerializerInterface $serializer): JsonResponse
    {
        $users = $membreRepository->findAll();

        $usersNotesMoyennes = [];
        foreach ($users as $user) {
            $noteMoyenne = $user->getNoteMoyenne($entityManager);
            $usersNotesMoyennes[] = [
            'user' => $user,
            'noteMoyenne' => $noteMoyenne,
            'nbNotes' => $user->getNbNotes($entityManager),
            ];
        }
        // On enlève les membres qui n'ont pas donné de note
        $usersNotesMoyennes = array_filter($usersNotesMoyennes, function ($userNote) {
            return $userNote['noteMoyenne'] !== null;
        });
        // On fait un classement par ordre décroissant
        usort($usersNotesMoyennes, function ($a, $b) {
            return $a['noteMoyenne'] <=> $b['noteMoyenne'];
        });

        $jsonUsersNotesMoyennes = $serializer->serialize($usersNotesMoyennes, 'json');

        return new JsonResponse($jsonUsersNotesMoyennes, Response::HTTP_OK, [], true);
    }

}
