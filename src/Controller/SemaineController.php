<?php

namespace App\Controller;


use App\Service\PrintSemaine;
use App\Repository\SemaineRepository;
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

    #[Route('/printSemaine', name: 'printSemaine', methods: ['GET'])]
    public function printSemaine(PrintSemaine $printSemaine, SerializerInterface $serializer): JsonResponse
    {
        $semaineList = $printSemaine->getSemaine();
        $jsonSemainelist = $serializer->serialize($semaineList, 'json');
        return new JsonResponse ($jsonSemainelist, Response::HTTP_OK, [], true);

    }
}
?>