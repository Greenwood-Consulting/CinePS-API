<?php

namespace App\Controller;

use App\Entity\Film;
use App\Repository\FilmRepository;
use JMS\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use  Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class FilmController extends AbstractController
{
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


    #[Route('/api/Allfilms', name: 'app_Allfilms')]
    public function getAllFilms(EntityManagerInterface $entityManager, FilmRepository $filmRepository, SerializerInterface $serializer): JsonResponse
    {

        //Récupérer le film de la semaine qui a le score le plus élevé
        $queryBuilder_get_film = $entityManager->createQueryBuilder();
        $queryBuilder_get_film->select('f')
        ->from(Film::class, 'f')
        ->orderBy('f.sortie_film', 'DESC');

        $get_film = $queryBuilder_get_film->getQuery()->getResult();
        $jsonFilm = $serializer->serialize($get_film, 'json');

        return new JsonResponse ($jsonFilm, Response::HTTP_OK, [], true);
    }

    
}

?>