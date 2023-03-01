<?php

namespace App\Controller;

use App\Repository\MembreRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MembreController extends AbstractController
{
    #[Route('/api/membres', name: 'app_membre')]
    public function getAllMembres(MembreRepository $membreRepository, SerializerInterface $serializer): JsonResponse
    {

        $membreList = $membreRepository->findAll();

        $jsonMembreList = $serializer->serialize($membreList, 'json');
        return new JsonResponse($jsonMembreList, Response::HTTP_OK, [], true);
    }


    #[Route('/api/membres/{id}', name: 'detailMembre', methods: ['GET'])]
    public function getDetailMembre(int $id, SerializerInterface $serializer, MembreRepository $membreRepository): JsonResponse
    {

        $membre = $membreRepository->find($id);
        if($membre) {
            $jsonMembre = $serializer->serialize($membre, 'json');
            return new JsonResponse($jsonMembre, Response::HTTP_OK, [], true);
        }
        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }
}
