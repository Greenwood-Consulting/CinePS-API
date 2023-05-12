<?php

namespace App\Controller;

use App\Entity\Semaine;
use JMS\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class IsPropositionTermineeController extends AbstractController
{
    #[Route('/isPropositionTerminee/{id_semaine}', name: 'app_is_proposition_terminee')]
    public function isPropositionTerminee(int $id_semaine, EntityManagerInterface $entityManager, SerializerInterface $serializer): JsonResponse
    {
        //Récupère la propositionTerminé de id_semaine
        $queryBuilder_get_jour = $entityManager->createQueryBuilder();
        $queryBuilder_get_jour->select('s.id, COALESCE(s.proposition_termine, 0) as proposition_termine')
        ->from(Semaine::class, 's')
        ->where('s.id = :id')
        ->setParameter('id', $id_semaine);


        

        $resultat_proposition_terminee = $queryBuilder_get_jour->getQuery()->getResult();

        if (empty($resultat_proposition_terminee)) {
            $resultat_controller = $serializer->serialize([0 => ['proposition_termine' => 0]], 'json');
        } else {
            $jsonResultatsPropositionTerminee = $serializer->serialize($resultat_proposition_terminee, 'json');
            $resultat_controller = $jsonResultatsPropositionTerminee;
        }


        return new JsonResponse ($resultat_controller, Response::HTTP_OK, [], true);

        
        // // TODO: Construire une réponse identique lorsque qu'il n'y a pas de semaine existante
        // if(count($resultat_proposition_terminee) === 0){
        //     $resultat_controller = new JsonResponse(json_encode(['proposition_termine'=> 0]));
        // }else{
        //      
        // }
        // 
    }
}
