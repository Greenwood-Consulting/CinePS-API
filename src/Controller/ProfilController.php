<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class ProfilController extends AbstractController
{
    
    // Récupère tous les films gagnants de toutes les semaines passées
    #[Route('/api/filmsGagnants', name:'filmsGagants', methods: ['GET'])]
    public function filmsGagnants(EntityManagerInterface $entityManager, SerializerInterface $serializer): JsonResponse
    {
        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('f')
            ->from('App\Entity\Film', 'f')
            ->leftJoin('App\Entity\Proposition', 'p', 'WITH', 'p.film = f.id')
            ->leftJoin('App\Entity\Semaine', 's', 'WITH', 's.id = p.semaine')
            ->where('s.propositionGagnante IS NOT NULL AND s.propositionGagnante = p.id')
            ->orWhere('s.propositionGagnante IS NULL AND p.score = (
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