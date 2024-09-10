<?php

namespace App\Controller;

use App\Entity\Semaine;
use App\Repository\MembreRepository;
use App\Repository\SemaineRepository;
use JMS\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class StatsController extends AbstractController
{
    //Récupère le nombre de proposeur 
    #[Route('/api/getNbPropositionsParProposeur', name: 'app_get_nbproposeur')]
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

}
