<?php

namespace App\Controller;

use App\Entity\AVote;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AVoteController extends AbstractController
{
    //Afficher les membres ayant voté pour la semaine
    #[Route('/membreVotant/{id_semaine}', name:'membreVotant', methods: ['GET'])]
    public function membreVotant(int $id_semaine, EntityManagerInterface $entityManager, SerializerInterface $serializer): JsonResponse
    {
        //Récupérer les membre ayant voté
        $queryBuilder_get_membre_votant = $entityManager->createQueryBuilder();
        $queryBuilder_get_membre_votant->select('a')
        ->from(AVote::class, 'a')
        ->where('a.semaine = :semaine')
        ->setParameter('semaine', $id_semaine);

        $membre_votant = $queryBuilder_get_membre_votant->getQuery()->getResult();
        $jsonMembreVotant = $serializer->serialize($membre_votant, 'json');

        return new JsonResponse ($jsonMembreVotant, Response::HTTP_OK, [], true);
    }
}
