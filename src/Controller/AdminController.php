<?php

namespace App\Controller;

use DateTime;
use App\Entity\Membre;
use App\Entity\Semaine;
use App\Repository\MembreRepository;
use App\Repository\SemaineRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;




class AdminController extends AbstractController
{
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

    #[Route('/api/newSemaine', name:"createSemaine", methods: ['POST'])]
    public function createSemaine(Request $request, MembreRepository $membreRepository, SerializerInterface $serializer, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator): JsonResponse 
    {

        $array_request = json_decode($request->getContent(), true);
        $membre = $membreRepository->findOneById($array_request['proposeur_id']);
        $jour = DateTime::createFromFormat('Y-m-d', $array_request['jour']);
        $typeSemaine = $array_request['type'];

        $new_semaine = new Semaine();
        $new_semaine->setProposeur($membre);
        $new_semaine->setJour($jour);
        $new_semaine->setPropositionTermine(false);
        $new_semaine->setTheme("");
        $new_semaine->settype($typeSemaine);


        $em->persist($new_semaine);
        $em->flush();

        $jsonSemaine = $serializer->serialize($new_semaine, 'json', ['groups' => 'getPropositions']);
        
        $location = $urlGenerator->generate('detailSemaine', ['id' => $new_semaine->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonSemaine, Response::HTTP_CREATED, ["Location" => $location], true);
    }



}
