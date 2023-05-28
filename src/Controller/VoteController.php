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
    // Retourner le film victorieux de la semaine id_semaine
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
