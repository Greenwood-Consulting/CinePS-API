<?php

namespace App\Controller;

use App\Entity\Semaine;
use App\Repository\MembreRepository;
use JMS\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class GetProposeurController extends AbstractController
{
    //Récupère le proposeur de la semaine $id_semaine
    #[Route('/api/getProposeur/{id_semaine}', name: 'app_get_proposeur')]
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



    //Work In Progress
    #[Route('/api/getCurrentProposeur/{id_semaine}', name: 'app_get_current_proposeur')]
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

    //Récupère le nombre de proposeur 
    #[Route('/api/getNbPropositionsParProposeur', name: 'app_get_proposeur')]
    public function getCountProposeurSemaine(EntityManagerInterface $entityManager, SerializerInterface $serializer): JsonResponse
    {
        $queryBuilder_get_nb_propositions_par_proposeur = $entityManager->createQueryBuilder();
        $queryBuilder_get_nb_propositions_par_proposeur->select('p.Nom AS proposeur','COUNT(s.id) AS nb_semaines')
        ->from(Semaine::class, 's')
        ->innerJoin('s.proposeur' ,'p')
        ->groupBy('s.proposeur');
        
        $Resultat_nb_propositions_par_proposeur = $queryBuilder_get_nb_propositions_par_proposeur->getQuery()->getResult();
        $jsonResultatNbPropositionParProposeur = $serializer->serialize($Resultat_nb_propositions_par_proposeur, 'json');
        
        if(isset($jsonResultatNbPropositionParProposeur))
        return new JsonResponse ($jsonResultatNbPropositionParProposeur, Response::HTTP_OK, [], true);

        
    }
}
