<?php

namespace App\Controller;

use App\Entity\Proposition;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\PropositionRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PropositionController extends AbstractController
{
    #[Route('/api/propositions', name: 'app_proposition')]
    public function getAllPropositions(PropositionRepository $propositionRepository, SerializerInterface $serializer): JsonResponse
    {

        $propositionList = $propositionRepository->findAll();

        $jsonPropositionList = $serializer->serialize($propositionList, 'json');
        return new JsonResponse($jsonPropositionList, Response::HTTP_OK, [], true);
    }

    #[Route('/api/propositions/{id}', name: 'detailProposition', methods: ['GET'])]
    public function getDetailFilm(int $id, SerializerInterface $serializer, PropositionRepository $propostionRepository): JsonResponse
    {

        $proposition = $propostionRepository->find($id);
        if($proposition) {
            $jsonProposition = $serializer->serialize($proposition, 'json');
            return new JsonResponse($jsonProposition, Response::HTTP_OK, [], true);
        }
        return new JsonResponse(["error" => "Not Found"], 404);
    }

    #[Route('/api/propositions/{id}', name: 'deleteProposition', methods: ['DELETE'])]
    public function deleteProposition(Proposition $proposition, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($proposition);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

}
