<?php

namespace App\Controller;

use App\Entity\Membre;
use App\Entity\Semaine;
use App\Repository\MembreRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Hateoas\Serializer\SerializerInterface as SerializerSerializerInterface;

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
