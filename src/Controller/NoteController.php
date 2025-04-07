<?php

namespace App\Controller;

use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Annotation\Model;
use App\Entity\Note;
use App\Repository\FilmRepository;
use App\Repository\MembreRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\PropositionRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class NoteController extends AbstractController
{
    #[OA\Tag(name: 'Note')]
    #[OA\Post(
        path: '/api/note',
        summary: 'Créer une nouvelle note',
        description: 'Permet de créer une nouvelle note pour un film par un membre. Si aucune note n\'est fournie, cela correspond à une abstention.',
        requestBody: new OA\RequestBody(
            description: 'Données nécessaires pour créer une note',
            required: true,
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(property: 'membre_id', type: 'integer', description: 'ID du membre notateur'),
                    new OA\Property(property: 'film_id', type: 'integer', description: 'ID du film à noter'),
                    new OA\Property(property: 'note', type: 'integer', nullable: true, description: 'Note attribuée au film (optionnelle)')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Note créée avec succès',
                content: new OA\JsonContent(ref: new Model(type: Note::class, groups: ['getNotes']))
            ),
            new OA\Response(response: 400, description: 'Requête invalide'),
            new OA\Response(response: 404, description: 'Membre ou film introuvable')
        ]
    )]
    // Crée une nouvelle note
    #[Route('/api/note', name: 'createNote', methods: ['POST'])]
    public function createNote(Request $request, MembreRepository $membreRepository, FilmRepository $filmRepository, SerializerInterface $serializer, EntityManagerInterface $em): JsonResponse
    {
        $array_request = json_decode($request->getContent(), true);
        $notateur = $membreRepository->findOneById($array_request['membre_id']);
        $film = $filmRepository->findOneById($array_request['film_id']);

        $note = new Note();
        $note->setMembre($notateur);
        $note->setFilm($film);
        if (isset($array_request['note'])){
            $note->setNote($array_request['note']);
        }
        else { // si on fait un call à ce service sans note, alors cela correspond à une abstention, qui est représentée en base par une note à null
            $note->setNote(null);
        }

        $em->persist($note);
        $em->flush();

        $jsonNote = $serializer->serialize($note, 'json', ['groups' => 'getNotes']); 
        return new JsonResponse($jsonNote, Response::HTTP_CREATED, [], true);
    }
}