<?php

namespace App\Controller;

use App\Entity\Proposition;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PropositionController extends AbstractController
{

    //Crée une nouvelle proposition
    #[Route('/api/proposition', name: 'detailProposition', methods: ['POST'])]
    public function createProposition(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator): JsonResponse
    {
        $proposition = $serializer->deserialize($request->getContent(), Proposition::class, 'json');
        echo "<pre> Proposition :"; // DEBUG
        print_r($proposition);
        echo "</pre>";
        $em->persist($proposition);
        $em->flush();

        $jsonProposition = $serializer->serialize($proposition, 'json', ['groups' => 'getBooks']);
        
        $location = $urlGenerator->generate('detailBook', ['id' => $proposition->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonProposition, Response::HTTP_CREATED, ["Location" => $location], true);
    }

}
