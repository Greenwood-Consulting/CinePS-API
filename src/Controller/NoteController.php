<?php

namespace App\Controller;

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
        else {
            $note->setNote(null);
        }

        $em->persist($note);
        $em->flush();

        $jsonNote = $serializer->serialize($note, 'json', ['groups' => 'getNotes']); 
        return new JsonResponse($jsonNote, Response::HTTP_CREATED, [], true);
    }
}