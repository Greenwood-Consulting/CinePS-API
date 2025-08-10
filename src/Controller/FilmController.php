<?php

namespace App\Controller;

use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Annotation\Model;
use App\Entity\Film;
use App\Entity\PreSelection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use  Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\DTO\CreateFilmInput;

class FilmController extends AbstractController
{
    #[OA\Get(
        path: '/api/films/{id}',
        summary: 'Récupère un film par son identifiant',
        tags: ['Films'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'Identifiant du film',
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Film trouvé',
                content: new OA\JsonContent(ref: new Model(type: Film::class, groups: ['film:read']))
            ),
            new OA\Response(
                response: 404,
                description: 'Film non trouvé'
            )
        ]
    )]
    #[Route('/api/films/{id}', name: 'film_find', methods: ['GET'])]
    public function find(Film $film, SerializerInterface $serializer): JsonResponse
    {
        $json = $serializer->serialize($film, 'json', ['groups' => ['film:read']]);
        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }


    #[OA\Post(
        path: '/api/films',
        summary: 'Créer un nouveau film',
        tags: ['Films'],
        requestBody: new OA\RequestBody(
            required: true,
            description: 'Données du film à créer',
            content: new OA\JsonContent(
                ref: new Model(type: CreateFilmInput::class, groups: ['film:write'])
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Film créé avec succès',
                content: new OA\JsonContent(ref: new Model(type: Film::class, groups: ['film:read']))
            ),
            new OA\Response(
                response: 422,
                description: 'Erreur de validation'
            ),
            new OA\Response(
                response: 404,
                description: 'Pre-Selection not found'
            )
        ]
    )]
    #[Route('/api/films', name: "film_create", methods:['POST'])]
    public function create(Request $request, EntityManagerInterface $em, SerializerInterface $serializer, ValidatorInterface $validator): JsonResponse 
    {
        // le champ date pour un film represente la date de la proposition de ce film!! ce champ devrait être deplacé dans la table proposition
        // le champ en entrée est une date time avec potentiellement une timezone
        // le champ date en base ne contient que la date, sans information de timezone, la date sera tronquée simplement 
        // new Date generé depuis le serveur gandhi (lors de la creation d'un film + proposition) est probablement en UTC
        // si la timezone n'est pas spécifiée on utilisera l'heure de Paris par defaut
        $input = $serializer->deserialize($request->getContent(), CreateFilmInput::class, 'json', ['groups' => ['film:write'], 'datetime_timezone' => 'Europe/Paris']);
        // lors des test utiliser "date": "2025-07-07T20:30:00-10:00", (timezone Alaska !)
        // et verifier en base que 2025-07-08 est enregistré
        
        // Validation de l'entité Film
        $errors = $validator->validate($input);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
            return new JsonResponse(['errors' => $errorMessages], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        $newFilm = new Film();
        $newFilm->setTitre($input->titre);
        // pour le stockage en BDD, on force la timezone UTC par convention
        $newFilm->setDate($input->date->setTimezone(new \DateTimeZone('UTC')));
        $newFilm->setSortieFilm($input->sortie_film);
        $newFilm->setImdb($input->imdb);

        // Récuperer l'eventuelle preselection
        if (isset($input->pre_selection_id)) {
            $preSelectionObj = $em->getRepository(PreSelection::class)->find($input->pre_selection_id);
            if (!$preSelectionObj) {
                return new JsonResponse(['error' => 'Pre-Selection not found.'], Response::HTTP_NOT_FOUND);
            }

            // add film to a proposition
            $newFilm->addPreSelection($preSelectionObj);
        }

        $em->persist($newFilm);
        $em->flush();
        // refresh $newFilm to get the truncated date
        $em->refresh($newFilm);

        $jsonFilm = $serializer->serialize($newFilm, 'json', ['groups' => ['film:read']]);
        return new JsonResponse($jsonFilm, JsonResponse::HTTP_CREATED, [], true);
    }

    
    #[OA\Delete(
        path: "/api/films/{id}",
        summary: "Supprimer un film",
        description: "Supprime un film par son identifiant.",
        operationId: "deleteFilm",
        tags: ["Films"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                description: "Identifiant du film à supprimer",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 204,
                description: "Film supprimé ou n'existe pas"
            ),
            new OA\Response(
                response: 409,
                description: "Impossible de supprimer un film avec des propositions ou des notes associées",
            )
        ]
    )]
    #[Route('/api/films/{id}', name:"film_delete", methods:['DELETE'])]
    public function delete(?Film $film, EntityManagerInterface $em): JsonResponse
    {
        // Comportement idempotent : si le film n'existe pas, on retourne quand même 204
        if (!$film) {
            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        }

        // Si le film a des propositions, on bloque la suppression
        if (count($film->getPropositions()) > 0) {
            return new JsonResponse(
                ['error' => 'Cannot delete a film with associated propositions'],
                Response::HTTP_CONFLICT
            );
        }

        // Si le film à été noté, on bloque la suppression
        if (count($film->getNotes()) > 0) {
            return new JsonResponse(
                ['error' => 'Cannot delete a film with associated notes'],
                Response::HTTP_CONFLICT
            );
        }

        $em->remove($film);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    
    // @TODO : est-ce que ce contrôleur est utilisé ? Non
    // actuellement reservé au role admin ?
    #[OA\Put(
        path: "/api/films/{id}",
        summary: "Modifier un film existant",
        tags: ["Films"],
        requestBody: new OA\RequestBody(
            description: "Données du film à modifier",
            required: true,
            content: new OA\JsonContent(
                ref: new Model(type: Film::class, groups: ['film:write'])
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
                response: 404,
                description: "Film non trouvé"
            )
        ]
    )]
    #[Route('/api/films/{id}', name:"film_update", methods:['PUT'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour éditer un film')]
    public function updateFilm(Request $request, SerializerInterface $serializer, Film $currentFilm, EntityManagerInterface $em, ValidatorInterface $validator, TagAwareCacheInterface $cache): JsonResponse 
    {
        $newFilm = $serializer->deserialize($request->getContent(), Film::class, 'json', ['groups' => ['film:write'], 'datetime_timezone' => 'UTC']);
        
        $currentFilm->setTitre($newFilm->getTitre());
        $currentFilm->setDate($newFilm->getDate());
        $currentFilm->setSortieFilm($newFilm->getSortieFilm());
        $currentFilm->setImdb($newFilm->getImdb());

        // On vérifie les erreurs
        $errors = $validator->validate($currentFilm);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $em->persist($currentFilm);
        $em->flush();

        // On vide le cache.
        $cache->invalidateTags(["filmsCache"]);

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }

}

?>