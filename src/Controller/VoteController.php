<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class VoteController extends AbstractController
{
    #[Route('/api/votes', name: 'app_vote')]
    public function getAllVotes(VoteRepository $voteRepository, SerializerInterface $serializer): JsonResponse
    {

        $voteList = $voteRepository->findAll();

        $jsonVoteList = $serializer->serialize($voteList, 'json');
        return new JsonResponse($jsonVoteList, Response::HTTP_OK, [], true);
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
