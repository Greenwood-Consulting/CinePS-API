<?php

namespace App\Controller;

use App\Entity\Semaine;
use App\Repository\MembreRepository;
use App\Repository\SemaineRepository;
use JMS\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class GetProposeurController extends AbstractController
{
    //Récupère le proposeur de la semaine $id_semaine
    #[Route('/api/getProposeur/{id_semaine}', name: 'app_get_proposeur', methods: ['GET'])]
    public function getProposeur(int $id_semaine, SemaineRepository $semaineRepository, EntityManagerInterface $entityManager, SerializerInterface $serializer): JsonResponse
    {
        $semaine = $semaineRepository->find($id_semaine);
        $proposeur = $semaine->getProposeur();

        $jsonProposeur = $serializer->serialize($proposeur, 'json');

        return new JsonResponse($jsonProposeur, Response::HTTP_OK, [], true);

    }

}
