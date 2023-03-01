<?php

namespace App\Controller;

use App\Entity\Film;
use App\Repository\FilmRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use  Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class FilmController extends AbstractController
{
    #[Route('/api/films', name: 'app_film', methods: ['GET'])]
    public function getAllFilms(FilmRepository $filmRepository, SerializerInterface $serializer, Request $request): JsonResponse
    {

        $page = $request->get('page', 1);
        $limit = $request->get('limit', 3);

        $filmList = $filmRepository->findAllWithPagination($page, $limit);

        $jsonFilmList = $serializer->serialize($filmList, 'json');
        return new JsonResponse($jsonFilmList, Response::HTTP_OK, [], true);
    }


    #[Route('/api/films/{id}', name: 'detailFilm', methods: ['GET'])]
    public function getDetailFilm(int $id, SerializerInterface $serializer, FilmRepository $filmRepository): JsonResponse
    {

        $film = $filmRepository->find($id);
        if($film) {
            $jsonFilm = $serializer->serialize($film, 'json');
            return new JsonResponse($jsonFilm, Response::HTTP_OK, [], true);
        }
        return new JsonResponse(["error" => "Not Found"], 404);
    }

    #[Route('/api/films/{id}', name: 'deleteFilm', methods: ['DELETE'])]
    public function deleteFilm(Film $film, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($film);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
    
    #[Route('/api/films', name:"createFilm", methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour proposer un film')]
    public function createFilm(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator, ValidatorInterface $validator): JsonResponse 
    {

        $film = $serializer->deserialize($request->getContent(), Film::class, 'json');

        // On vérifie les erreurs
        $errors = $validator->validate($film);

        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }



        $em->persist($film);
        $em->flush();

        $jsonFilm = $serializer->serialize($film, 'json', ['groups' => 'getFilms']);
        
        $location = $urlGenerator->generate('detailFilm', ['id' => $film->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonFilm, Response::HTTP_CREATED, ["Location" => $location], true);
   }
}


