<?php

namespace App\Controller;

use OpenApi\Attributes as OA;
use App\Service\CurrentSemaine;
use Symfony\Component\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class IsPropositionTermineeController extends AbstractController
{

    #[OA\Get(
        path: "/api/isVoteTermine/{id_semaine}",
        summary: "Vérifie si le vote pour une semaine est terminé",
        parameters: [
            new OA\Parameter(
            name: "id_semaine",
            in: "path",
            required: true,
            description: "ID de la semaine",
            schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Indique si le vote est terminé",
                content: new OA\JsonContent(
                    type: "boolean",
                    example: true
                )
            ),
            new OA\Response(
                response: 404,
                description: "Semaine non trouvée"
            )
        ]
    )]
    //Indique si le vote pour la semaine $id_semaine est terminée
    // Deprecated: use value in GET /api/currentSemaine instead
    #[Route('/api/isVoteTermine/{id_semaine}', name: 'is_vote_termine', methods: ['GET'])]
    public function isVoteTermineCetteSemaine(CurrentSemaine $currentSemaine, EntityManagerInterface $em, SerializerInterface $serializer): JsonResponse
    {
        $isVoteTermine = $currentSemaine->isVoteTermine($em);
        $jsonVote_termine_cette_semaine = $serializer->serialize($isVoteTermine, 'json');
        return new JsonResponse ($jsonVote_termine_cette_semaine, Response::HTTP_OK, [], true);
    }
}
