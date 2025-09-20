<?php

namespace App\Controller;

use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Membre;
use App\Entity\PreSelection;
use App\DTO\PreSelectionInput;

class PreSelectionController extends AbstractController
{

    #[OA\Tag(name: 'PreSelections')]
    #[OA\Get(
        path: '/api/preselections/{id}',
        summary: 'Récupère une pré-sélection',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'Identifiant de la pré-sélection',
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Pré-sélection trouvée',
                content: new OA\JsonContent(
                    ref: new Model(type: PreSelection::class, groups: ['preselection:read'])
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Pré-sélection non trouvée'
            )
        ]
    )]  
    #[Route('/api/preselections/{id}', name: 'preselections_find', methods: ['GET'])]
    public function find(PreSelection $preSelection, SerializerInterface $serializer): JsonResponse
    {
        $json = $serializer->serialize($preSelection, 'json', ['groups' => ['preselection:read']]);
        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }
    

    // TODO: if identification (via JWT token or else) is available, replace Route '/membres/{id}' by ''
    #[OA\Tag(name: 'PreSelections')]
    #[OA\Get(
        path: '/api/preselections/membres/{id}',
        summary: 'Liste les pré-sélections associées à un membre',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'Identifiant du membre',
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Liste des pré-sélections trouvées',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        ref: new Model(type: PreSelection::class, groups: ['preselection:read'])
                    )
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Membre non trouvé'
            )
        ]
    )]
    #[Route('/api/preselections/membres/{id}', name: 'preselections_list', methods: ['GET'])]
    public function listByMembre(Membre $membre, EntityManagerInterface $em, SerializerInterface $serializer): JsonResponse
    {
        $preSelections = $em->getRepository(PreSelection::class)->findBy(['membre' => $membre]);
        $json = $serializer->serialize($preSelections, 'json', ['groups' => ['preselection:read']]);
        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }


    #[OA\Tag(name: 'PreSelections')]
    #[OA\Post(
        path: '/api/preselections',
        summary: 'Créer une présélection',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: new Model(type: PreSelectionInput::class, groups: ['preselection:write'])
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Présélection créée',
                content: new OA\JsonContent(
                    ref: new Model(type: PreSelection::class, groups: ['preselection:read'])
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Requête invalide'
            ),
            new OA\Response(
                response: 404,
                description: 'Membre introuvable'
            ),
            new OA\Response(
                response: 422,
                description: 'Erreurs de validation'
            )
        ]
    )]
    #[Route('/api/preselections', name: 'preselections_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em, SerializerInterface $serializer, ValidatorInterface $validator): JsonResponse
    {
        // TODO: recuperer le membre identifié par token JWT
        // cette fonction ne devrait pas permettre de creer une preselection pour autrui
        
        $input = $serializer->deserialize($request->getContent(), PreSelectionInput::class, 'json', ['groups' => ['preselection:write']]);
        
        $membre = $em->getRepository(Membre::class)->find($input->membre_id);
        if (!$membre) {
            return new JsonResponse(['error' => 'Membre not found.'], Response::HTTP_NOT_FOUND);
        }

        $newPreSelection = new PreSelection();
        $newPreSelection->setTheme($input->theme);
        $newPreSelection->setMembre($membre);

        $em->persist($newPreSelection);
        $em->flush();
        $jsonPreSelection = $serializer->serialize($newPreSelection, 'json', ['groups' => ['preselection:read']]);
        return new JsonResponse($jsonPreSelection, Response::HTTP_CREATED, [], true);
    }


    #[OA\Tag(name: 'PreSelections')]
    #[OA\Delete(
        path: '/api/preselections/{id}',
        summary: 'Supprime une présélection',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'Identifiant de la présélection à supprimer',
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 204,
                description: 'Suppression réussie ou déjà inexistante (idempotent)'
            ),
            new OA\Response(
                response: 404,
                description: 'Présélection introuvable (si type-hinting ou param converter échoue)'
            )
        ]
    )]
    #[Route('/api/preselections/{id}', name: 'preselections_delete', methods: ['DELETE'])]
    public function delete(?PreSelection $preSelection, EntityManagerInterface $em): JsonResponse
    {
        // Suppression idempotente
        if ($preSelection) {
            $em->remove($preSelection);
            $em->flush();
        }
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
