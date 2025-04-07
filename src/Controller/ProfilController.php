<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use OpenApi\Attributes as OA;

class ProfilController extends AbstractController
{
    #[OA\Tag(name: 'Profil')]
    #[OA\Get(
        path: '/api/filmsGagnants',
        summary: 'Retrieve all winning films from past weeks',
        tags: ['Profil'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of winning films',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/Film')
                )
            )
        ]
    )]
    // Récupère tous les films gagnants de toutes les semaines passées
    #[Route('/api/filmsGagnants', name:'filmsGagants', methods: ['GET'])]
    public function filmsGagnants(EntityManagerInterface $entityManager, SerializerInterface $serializer): JsonResponse
    {
        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('f')
            ->from('App\Entity\Film', 'f')
            ->leftJoin('App\Entity\Proposition', 'p', 'WITH', 'p.film = f.id')
            ->leftJoin('App\Entity\Semaine', 's', 'WITH', 's.id = p.semaine')
            ->where('s.filmVu IS NOT NULL AND s.filmVu = p.id')
            ->orWhere('s.filmVu IS NULL AND p.score = (
            SELECT MAX(p2.score) 
            FROM App\Entity\Proposition p2 
            WHERE p2.semaine = s.id
            )')
            ->andWhere('p.id = (
            SELECT MIN(p3.id)
            FROM App\Entity\Proposition p3
            WHERE p3.semaine = s.id AND p3.score = p.score
            )')
            ->andWhere('s.jour < :today')
            ->setParameter('today', new \DateTime())
            ->orderBy('s.jour', 'DESC');

        $filmsGagnants = $queryBuilder->getQuery()->getResult();
        $serializedFilmsGagnants = $serializer->serialize($filmsGagnants, 'json', ['groups' => 'filmsGagnants']);
        return new JsonResponse($serializedFilmsGagnants, Response::HTTP_OK, [], true);

    }

}