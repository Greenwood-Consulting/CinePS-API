<?php

namespace App\Controller;

use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Annotation\Model;
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
    #[OA\Put(
        path: "/api/films/{id}",
        summary: "Modifier un film existant",
        requestBody: new OA\RequestBody(
            description: "Données du film à modifier",
            required: true,
            content: new OA\JsonContent(
                ref: new Model(type: Film::class, groups: ['updateFilm'])
            )
        ),
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                description: "Identifiant du film à modifier",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 204,
                description: "Film modifié avec succès"
            ),
            new OA\Response(
                response: 400,
                description: "Données invalides"
            ),
            new OA\Response(
                response: 403,
                description: "Accès refusé"
            ),
            new OA\Response(
                response: 404,
                description: "Film non trouvé"
            )
        ]
    )]
    //Permet à l'admin de modifier les films
    // @TODO : est-ce que ce contrôleur est utilisé ?
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

    #[OA\Get(
        path: "/api/Allfilms",
        summary: "Récupérer tous les films",
        responses: [
            new OA\Response(
                response: 200,
                description: "Liste des films récupérée avec succès",
                content: new OA\JsonContent(
                    type: "array",
                    items: new OA\Items(ref: new Model(type: Film::class, groups: ['getAllFilms']))
                )
            ),
            new OA\Response(
                response: 500,
                description: "Erreur serveur"
            )
        ]
    )]
    // @TODO est-ce que ce contrôleur est utilisé ?
    #[Route('/api/Allfilms', name: 'app_Allfilms', methods:['GET'])]
    public function getAllFilms(EntityManagerInterface $entityManager, FilmRepository $filmRepository, SerializerInterface $serializer): JsonResponse
    {

        //Récupére tous les films en les classant par ordre anthéchronologique
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