<?php

namespace App\Controller;


use App\Entity\Semaine;
use App\Service\PrintSemaine;
use App\Service\SemaineService;
use App\Repository\SemaineRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\PropositionRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use DateTime;


class SemaineController extends AbstractController
{
    #[Route('/api/semaines', name: 'app_semaine')]
    public function getAllSemaines(SemaineRepository $semaineRepository, SerializerInterface $serializer): JsonResponse
    {

        $semaineList = $semaineRepository->findAll();

        $jsonSemaineList = $serializer->serialize($semaineList, 'json');
        return new JsonResponse($jsonSemaineList, Response::HTTP_OK, [], true);
    }


    #[Route('/api/semaine/{id}', name: 'detailSemaine', methods: ['GET'])]
    public function getDetailSemaine(int $id, SerializerInterface $serializer, SemaineRepository $semaineRepository): JsonResponse
    {
        $semaine = $semaineRepository->find($id);
        if($semaine) {
            $jsonSemaine = $serializer->serialize($semaine, 'json');
            return new JsonResponse($jsonSemaine, Response::HTTP_OK, [], true);
        }
        return new JsonResponse(null, Response::HTTP_NOT_FOUND);    
    }

    // Retourne l'id base de données de la semaine en cours. 0 si la semaine en cours n'existe pas encore dans la base de données
    #[Route('/api/idCurrentSemaine', name: 'idCurrentSemaine', methods: ['GET'])]
    public function getIdCurrentSemaine(SerializerInterface $serializer, EntityManagerInterface $entityManager, SemaineRepository $semaineRepository): JsonResponse
    {
        // Date du jour
        $curdate=new DateTime();

        // calcul de la date de fin de la période de vote
        $fin_periode_vote = new DateTime("Fri 14:00");
        $fin_periode_vote = $fin_periode_vote->format('Y-m-d H:i:s');

        // conversion de la date de fin en timestamp
        $deadline_vote = strtotime($fin_periode_vote);
        $deadline_vote = $deadline_vote*1000;

        // Get vendredi id_current_semaine
        if ($curdate->format('D')=="Fri"){ // Si nous sommes vendredi, alors id_current_semaine est défini par ce vendredi
            $friday_current_semaine = $curdate->format('Y-m-d');
        } else { // Sinon id_current_semaine est défini par vendredi prochain
            $friday_current_semaine = $curdate->modify('next friday')->format('Y-m-d');
        }

        //Récupère la propositionTerminé de id_semaine
        $queryBuilder_get_id_current_semaine = $entityManager->createQueryBuilder();
        $queryBuilder_get_id_current_semaine->select('s.id')
        ->from(Semaine::class, 's')
        ->where('s.jour = :jour')
        ->setParameter('jour', $friday_current_semaine);

        $result_id_current_semaine = $queryBuilder_get_id_current_semaine->getQuery()->getResult();
        
        if ($result_id_current_semaine){
            $id_current_semaine = $result_id_current_semaine[0]['id'];
        } else { // la semaine courrant n'exite pas encore dans la base de données
            $id_current_semaine = 0;
        }
        $array_id_current_semaine = array("id_current_semaine" => $id_current_semaine);
        $json_id_current_semaine = $serializer->serialize($array_id_current_semaine, 'json');
        return new JsonResponse ($json_id_current_semaine, Response::HTTP_OK, [], true);
    }

    #[Route('/filmsProposes/{id_semaine}', name: 'filmsProposes', methods: ['GET'])]
    public function filmsProposes(int $id_semaine, PropositionRepository $propositionRepository, SerializerInterface $serializer): JsonResponse
    {
        $filmsProposes = $propositionRepository->findBySemaine($id_semaine);
        $jsonFilmProposes = $serializer->serialize($filmsProposes, 'json');
        return new JsonResponse ($jsonFilmProposes, Response::HTTP_OK, [], true);

    }

    #[Route('/nextProposeurs/{id_semaine}', name:'nextProposeurs', methods: ['GET'])]
    public function nextProposeurs(int $id_semaine, EntityManagerInterface $entityManager, SerializerInterface $serializer): JsonResponse
    {
        //Récuperer le jour de la semaine $id_semaine
        $queryBuilder_get_jour = $entityManager->createQueryBuilder();
        $queryBuilder_get_jour->select('s.jour')
        ->from(Semaine::class, 's')
        ->where('s.id = :id')
        ->setParameter('id', $id_semaine);

        $resultats_jour = $queryBuilder_get_jour->getQuery()->getResult();

        //Récuperer les proposeurs des semaines postérieurs au jour précédent récupéré

        $queryBuilder_get_proposeurs = $entityManager->createQueryBuilder();
        $queryBuilder_get_proposeurs->select('s.proposeur','s.jour')
        ->from(Semaine::class, 's')
        ->where('s.jour >= :jour')
        ->setParameter('jour', $resultats_jour[0]['jour']);

        $resultats_proposeurs = $queryBuilder_get_proposeurs->getQuery()->getResult();
        $jsonResultatsProposeurs = $serializer->serialize($resultats_proposeurs, 'json');

        return new JsonResponse ($jsonResultatsProposeurs, Response::HTTP_OK, [], true);

    }
}
?>