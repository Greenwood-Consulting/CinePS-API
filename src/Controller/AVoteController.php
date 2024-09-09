<?php

namespace App\Controller;

use App\Entity\AVote;
use App\Service\CurrentSemaine;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AVoteController extends AbstractController
{
    
    // Indique si l'utilisateur a voté pour la semamine en cours
    #[Route('/api/aVoteCurrentSemaine/{id_membre}', name:'AVoteCurrentSemaine', methods: ['GET'])]
    public function aVoteCurrentSemaine(CurrentSemaine $currentSemaine, int $id_membre, EntityManagerInterface $entityManager, SerializerInterface $serializer): JsonResponse
    {
        $id_current_semaine = $currentSemaine->getIdCurrentSemaine($entityManager);

        $queryBuilder_get_aVote = $entityManager->createQueryBuilder();
        $queryBuilder_get_aVote->select('av.id')
        ->from(AVote::class, 'av')
        ->where('av.semaine = :semaine')
        ->andWhere('av.votant = :votant')
        ->setParameters(array('semaine' => $id_current_semaine, 'votant' => $id_membre));

        $resultats_aVote = $queryBuilder_get_aVote->getQuery()->getResult();

        if (empty($resultats_aVote)) {
            $result = $serializer->serialize(false, 'json');
        } else {
            $result = $serializer->serialize(true, 'json');
        }
        return new JsonResponse ($result, Response::HTTP_OK, [], true);

    }

}
