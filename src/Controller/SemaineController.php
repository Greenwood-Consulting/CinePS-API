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


class SemaineController extends AbstractController
{
    #[Route('/api/semaines', name: 'app_semaine')]
    public function getAllSemaines(SemaineRepository $semaineRepository, SerializerInterface $serializer): JsonResponse
    {

        $semaineList = $semaineRepository->findAll();

        $jsonSemaineList = $serializer->serialize($semaineList, 'json');
        return new JsonResponse($jsonSemaineList, Response::HTTP_OK, [], true);
    }


    #[Route('/api/semaines/{id}', name: 'detailSemaine', methods: ['GET'])]
    public function getDetailSemaine(int $id, SerializerInterface $serializer, SemaineRepository $semaineRepository): JsonResponse
    {

        $semaine = $semaineRepository->find($id);
        if($semaine) {
            $jsonSemaine = $serializer->serialize($semaine, 'json');
            return new JsonResponse($jsonSemaine, Response::HTTP_OK, [], true);
        }
        return new JsonResponse(null, Response::HTTP_NOT_FOUND);    
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