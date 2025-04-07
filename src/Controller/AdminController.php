<?php

namespace App\Controller;

use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Annotation\Model;
use DateTime;
use App\Entity\Membre;
use App\Entity\Semaine;
use App\Repository\MembreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminController extends AbstractController
{
    #[OA\Tag(name: 'Admin')]
    #[OA\Post(
        path: "/api/newmembre",
        summary: "Créer un nouveau membre",
        requestBody: new OA\RequestBody(
            description: "Données du nouveau membre",
            required: true,
            content: new OA\JsonContent(
                ref: new Model(type: Membre::class, groups: ['postMembre'])
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Membre créé avec succès",
                headers: [
                    new OA\Header(
                        header: "Location",
                        description: "URL du membre créé",
                        schema: new OA\Schema(type: "string", format: "uri")
                    )
                ],
                content: new OA\JsonContent(
                    ref: new Model(type: Membre::class, groups: ['getMembre'])
                )
            )
        ]
    )]
    #[Route('/api/newmembre', name:"createMembre", methods: ['POST'])]
    public function createMembre(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator): JsonResponse 
    {
        $membre = $serializer->deserialize($request->getContent(), Membre::class, 'json');
        $em->persist($membre);
        $em->flush();

        $jsonMembre = $serializer->serialize($membre, 'json', ['groups' => 'getMembre']);
        
        $location = $urlGenerator->generate('detailMembre', ['id' => $membre->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonMembre, Response::HTTP_CREATED, ["Location" => $location], true);
    }

    #[OA\Tag(name: 'Admin')]
    #[OA\Post(
        path: "/api/newSemaine",
        summary: "Créer une nouvelle semaine",
        requestBody: new OA\RequestBody(
            description: "Données de la nouvelle semaine",
            required: true,
            content: new OA\JsonContent(
                type: "object",
                properties: [
                    new OA\Property(property: "proposeur_id", type: "integer", description: "ID du proposeur"),
                    new OA\Property(property: "jour", type: "string", format: "date", description: "Jour de la semaine (format Y-m-d)"),
                    new OA\Property(property: "type_semaine", type: "string", description: "Type de la semaine")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Semaine créée avec succès",
                headers: [
                    new OA\Header(
                        header: "Location",
                        description: "URL de la semaine créée",
                        schema: new OA\Schema(type: "string", format: "uri")
                    )
                ],
                content: new OA\JsonContent(
                    ref: new Model(type: Semaine::class, groups: ['getPropositions'])
                )
            )
        ]
    )]
    #[Route('/api/newSemaine', name:"createSemaine", methods: ['POST'])]
    public function createSemaine(Request $request, MembreRepository $membreRepository, SerializerInterface $serializer, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator): JsonResponse 
    {
        $array_request = json_decode($request->getContent(), true);
        $membre = $membreRepository->findOneById($array_request['proposeur_id']);
        $jour = DateTime::createFromFormat('Y-m-d', $array_request['jour']);
        $typeSemaine = $array_request['type_semaine'];

        $new_semaine = new Semaine();
        $new_semaine->setProposeur($membre);
        $new_semaine->setJour($jour);
        $new_semaine->setPropositionTermine(false);
        $new_semaine->setTheme("");
        $new_semaine->setType($typeSemaine);


        $em->persist($new_semaine);
        $em->flush();

        $jsonSemaine = $serializer->serialize($new_semaine, 'json', ['groups' => 'getPropositions']);
        
        $location = $urlGenerator->generate('detailSemaine', ['id' => $new_semaine->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonSemaine, Response::HTTP_CREATED, ["Location" => $location], true);
    }

}
