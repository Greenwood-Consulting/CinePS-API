<?php

namespace App\Controller;

use App\Entity\Note;
use App\Repository\PropositionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use App\Repository\MembreRepository;


class NoteController extends AbstractController
{
// Crée une nouvelle note
#[Route('/api/note', name: 'createNote', methods: ['POST'])]
public function createNote(Request $request, MembreRepository $membreRepository, PropositionRepository $propositionRepository, SerializerInterface $serializer, EntityManagerInterface $em): JsonResponse
{
    $array_request = json_decode($request->getContent(), true);
    $notateur = $membreRepository->findOneById($array_request['membre_id']);
    $proposition = $propositionRepository->findOneById($array_request['proposition_id']);

    $note = new Note();
    $note->setMembre($notateur);
    $note->setProposition($proposition);
    $note->setNote($array_request['note']);

    $em->persist($note);
    $em->flush();

    $jsonNote = $serializer->serialize($note, 'json', ['groups' => 'getNotes']); 
    return new JsonResponse($jsonNote, Response::HTTP_CREATED, [], true);
}
}