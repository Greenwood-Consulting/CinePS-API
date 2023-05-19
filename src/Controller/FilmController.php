<?php

namespace App\Controller;

use App\Entity\Film;
use App\Service\PrintFilm;
use App\Service\HelloWorld;
use App\Repository\FilmRepository;
use JMS\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use  Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class FilmController extends AbstractController
{
    
    //Permet à l'admin d'ajouter un film
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

        $jsonFilm = $serializer->serialize($film, 'json');
        
        $location = $urlGenerator->generate('detailFilm', ['id' => $film->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonFilm, Response::HTTP_CREATED, ["Location" => $location], true);
   }


    //Permet à l'admin de modifier les films
    #[Route('/api/films/{id}', name:"updateFilm", methods:['PUT'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour éditer un film')]
    public function updateFilm(Request $request, SerializerInterface $serializer, Film $currentFilm, EntityManagerInterface $em, ValidatorInterface $validator, TagAwareCacheInterface $cache): JsonResponse 
    {
        $newFilm = $serializer->deserialize($request->getContent(), Film::class, 'json');
        $currentFilm->setTitre($newFilm->getTitre());
        $currentFilm->setDate($newFilm->getDate());
        $currentFilm->setSortieFilm($newFilm->getSortieFilm());
        $currentFilm->setImdb($newFilm->getImdb());

        // On vérifie les erreurs
        $errors = $validator->validate($currentFilm);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $content = $request->toArray();
        

        $em->persist($currentFilm);
        $em->flush();

        // On vide le cache.
        $cache->invalidateTags(["filmsCache"]);

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }
}

?>