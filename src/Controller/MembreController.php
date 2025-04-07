<?php

namespace App\Controller;

use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Annotation\Model;
use App\Entity\Membre;
use App\Repository\MembreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MembreController extends AbstractController
{
    #[OA\Tag(name: "Membre")]
    #[OA\Get(
        path: "/api/membres",
        summary: "Retrieve all membres",
        description: "Fetches a list of all membres",
        tags: ["Membre"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Successful response",
                content: new OA\JsonContent(
                    type: "array",
                    items: new OA\Items(ref: new Model(type: Membre::class))
                )
            ),
            new OA\Response(
                response: 404,
                description: "Membre not found"
            ),
            new OA\Response(
                response: 400,
                description: "Bad request"
            )
        ]
    )]
    #[Route('/api/membres', name: 'app_membre', methods: ['GET'])]
    public function getAllMembres(MembreRepository $membreRepository, SerializerInterface $serializer): JsonResponse
    {

        $membreList = $membreRepository->findAll();

        $jsonMembreList = $serializer->serialize($membreList, 'json');
        return new JsonResponse($jsonMembreList, Response::HTTP_OK, [], true);
    }

    #[OA\Tag(name: "Membre")]
    #[OA\Get(
        path: "/api/membres/{id}",
        summary: "Retrieve membre details",
        description: "Fetches details of a specific membre by ID",
        tags: ["Membre"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "The ID of the membre",
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Membre details retrieved successfully",
                content: new OA\JsonContent(ref: new Model(type: Membre::class))
            ),
            new OA\Response(
                response: 404,
                description: "Membre not found"
            )
        ]
    )]
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

    #[OA\Tag(name: "Membre")]
    #[OA\Patch(
        path: "/api/actifMembre/{id_membre}",
        summary: "Update membre activation status",
        description: "Modify the activation status of a membre by ID",
        tags: ["Membre"],
        parameters: [
            new OA\Parameter(
                name: "id_membre",
                in: "path",
                required: true,
                description: "The ID of the membre",
                schema: new OA\Schema(type: "integer")
            )
        ],
        requestBody: new OA\RequestBody(
            description: "Update membre activation status",
            required: true,
            content: new OA\JsonContent(
                type: "object",
                properties: [
                    new OA\Property(property: "actif", type: "boolean", description: "Activation status")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Membre activation status updated successfully",
                content: new OA\JsonContent(ref: new Model(type: Membre::class))
            ),
            new OA\Response(
                response: 404,
                description: "Membre not found"
            ),
            new OA\Response(
                response: 400,
                description: "Bad request"
            )
        ]
    )]
    // Modifier l'état d'activation d'un membre
    #[Route('/api/actifMembre/{id_membre}', name: 'actifMembre', methods: ['PATCH'])]
     public function updateMembre(int $id_membre, EntityManagerInterface $em, Request $request, SerializerInterface $serializer, MembreRepository $membreRepository): JsonResponse
    {
        $array_request = json_decode($request->getContent(), true);

        $membre = $membreRepository->findOneById($id_membre);
        
        if (isset($array_request['actif'])){
            $membre->setActif($array_request['actif']);
        }

        $em->persist($membre);
        $em->flush();

        $jsonProposition = $serializer->serialize($membre, 'json', ['groups' => 'getPropositions']); 
        return new JsonResponse($jsonProposition, Response::HTTP_OK, [], true);
    }
}
