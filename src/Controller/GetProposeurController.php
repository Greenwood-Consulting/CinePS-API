<?php

namespace App\Controller;

use App\Entity\Semaine;
use JMS\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class GetProposeurController extends AbstractController
{
    #[Route('/getProposeur/{id_semaine}', name: 'app_get_proposeur')]
    public function getProposeurSemaine(int $id_semaine, EntityManagerInterface $entityManager, SerializerInterface $serializer): JsonResponse
    {
        //Récupère la propositionTerminé de id_semaine
        $queryBuilder_get_jour = $entityManager->createQueryBuilder();
        $queryBuilder_get_jour->select('s.proposeur')
        ->from(Semaine::class, 's')
        ->where('s.id = :id')
        ->setParameter('id', $id_semaine);

        $resultat_proposeur_semaine = $queryBuilder_get_jour->getQuery()->getResult();
        $jsonResultatsProposeurSemaine = $serializer->serialize($resultat_proposeur_semaine, 'json');

        if(isset($resultat_proposeur_semaine)){
            return new JsonResponse ($jsonResultatsProposeurSemaine, Response::HTTP_OK, [], true);
        }
    }

    #[Route('/getCurrentProposeur', name: 'app_get_proposeur')]
    public function getCurrentProposeur(int $id_semaine, EntityManagerInterface $entityManager, SerializerInterface $serializer): JsonResponse
    {
        //Récupère la propositionTerminé de id_semaine
        $queryBuilder_get_jour = $entityManager->createQueryBuilder();
        $queryBuilder_get_jour->select('s.proposeur')
        ->from(Semaine::class, 's')
        ->where('s.id = :id')
        ->setParameter('id', $id_semaine);

        $resultat_proposeur_semaine = $queryBuilder_get_jour->getQuery()->getResult();
        $jsonResultatsProposeurSemaine = $serializer->serialize($resultat_proposeur_semaine, 'json');

        if(isset($resultat_proposeur_semaine)){
            return new JsonResponse ($jsonResultatsProposeurSemaine, Response::HTTP_OK, [], true);
        }
    }
}
