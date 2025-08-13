<?php

namespace App\Controller;

use App\Repository\GlobalSettingsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use OpenApi\Attributes as OA;


final class GlobalSettingsController extends AbstractController
{

    #[OA\Get(
        path: '/api/dlink',
        summary: 'Get the Dlink global setting',
        tags: ["Global Settings"],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns the Dlink value',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'value', type: 'string', example: 'https://example.com/dlink')
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Dlink not found'
            )
        ]
    )]
    #[Route('/api/dlink', name: 'global_settings_get_dlink', methods: ['GET'])]
    public function getDlink(GlobalSettingsRepository $globalSettingsRepository): JsonResponse
    {
        $dlink = $globalSettingsRepository->findOneByName('dlink');

        if (!$dlink) {
            return $this->json(['error' => 'Dlink not found'], Response::HTTP_NOT_FOUND);
        }
        return $this->json(['value' => $dlink->getValue()], Response::HTTP_OK);
    }


    #[OA\Put(
        path: '/api/dlink',
        summary: 'Update the Dlink global setting',
        tags: ["Global Settings"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(property: 'value', type: 'string', example: 'https://newlink.com')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 204,
                description: 'Dlink updated successfully'
            ),
            new OA\Response(
                response: 400,
                description: 'Missing "value" in request body'
            ),
            new OA\Response(
                response: 404,
                description: 'Dlink not found'
            )
        ]
    )]
    #[Route('/api/dlink', name: 'global_settings_update_dlink', methods: ['PUT'])]
    public function updateDlink(Request $request, GlobalSettingsRepository $globalSettingsRepository): JsonResponse {

        // validate input
        $data = json_decode($request->getContent(), true);
        if (!isset($data['value'])) {
            return $this->json(['error' => 'Missing "value" in request body'], Response::HTTP_BAD_REQUEST);
        }

        // get current "dlink"
        $dlink = $globalSettingsRepository->findOneByName('dlink');
        if (!$dlink) {
            return $this->json(['error' => 'Dlink not found'], Response::HTTP_NOT_FOUND);
        }

        // update
        $dlink->setValue($data['value']);
        $globalSettingsRepository->save($dlink, true); 

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

}
