<?php

namespace App\Controller;

use DateTime;
use App\Entity\Film;
use App\Entity\Vote;
use App\Entity\AVote;
use App\Entity\Proposition;
use App\Service\CurrentSemaine;
use App\Repository\MembreRepository;
use App\Repository\SemaineRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\PropositionRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MigrationController extends AbstractController
{

    #[Route('/api/membre/{prenom}', name: 'prenomMembre', methods: ['GET'])]
    public function getPrenomMembre(string $prenom, SerializerInterface $serializer, MembreRepository $membreRepository): JsonResponse
    {
        $membre = $membreRepository->findOneBy(['Prenom' => $prenom]);

        if ($membre) {
            $jsonMembre = $serializer->serialize($membre, 'json');
            return new JsonResponse($jsonMembre, Response::HTTP_OK, [], true);
        }

        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }

    // Crée une nouvelle proposition et le film associé
    #[Route('/api/propositionMigration', name: 'createPropositionMigration', methods: ['POST'])]
    public function migrationProposition(Request $request, SemaineRepository $semaineRepository, SerializerInterface $serializer, EntityManagerInterface $em): JsonResponse
    {
        $array_request = json_decode($request->getContent(), true);
        $semaine = $semaineRepository->find($array_request['id_semaine']);
        $film = new Film();
        $film->setTitre($array_request['titre_film']);
        $film->setDate(new DateTime());
        $film->setSortieFilm($array_request['sortie_film']);
        $film->setImdb($array_request['imdb_film'] );

        $proposition = new Proposition();
        $proposition->setSemaine($semaine);
        $proposition->setFilm($film);
        $proposition->setScore(36);

        $em->persist($film);
        $em->persist($proposition);
        $em->flush();

        $jsonProposition = $serializer->serialize($proposition, 'json', ['groups' => 'getPropositions']); 
        return new JsonResponse($jsonProposition, Response::HTTP_CREATED, [], true);
    }
    
    // Enregistre le vote et met à jour le score de la proposition
    #[Route('/api/saveVotePropositionMigration', name:"saveVotePropositionMigration", methods: ['POST'])]
    public function saveVotePropositionMigration(Request $request, CurrentSemaine $currentSemaine, SemaineRepository $semaineRepository, PropositionRepository $propositionRepository, MembreRepository $membreRepository, SerializerInterface $serializer, EntityManagerInterface $em): JsonResponse 
    {
        $array_request = json_decode($request->getContent(), true);
        $membre = $membreRepository->findOneById($array_request['membre']);
        $proposition = $propositionRepository->findOneById($array_request['proposition']);
        $proposition->setScore($proposition->getScore() - $array_request['vote']);
        $semaine = $semaineRepository->find($array_request['id_semaine']);

        $vote = new Vote();
        $vote->setSemaine($semaine);
        $vote->setMembre($membre);
        $vote->setProposition($proposition);
        $vote->setVote($array_request['vote']);

        $em->persist($vote);
        $em->flush();

        $jsonVote = $serializer->serialize($vote, 'json'); 
        return new JsonResponse($jsonVote, Response::HTTP_CREATED, [], true);
    }

    // Enregistre une nouvelle ligne dans la table 'AVote'
    #[Route('/api/avoteMigration', name:"aVoteMigration", methods: ['POST'])]
    public function avoteMigration(Request $request, CurrentSemaine $currentSemaine, SemaineRepository $semaineRepository, MembreRepository $membreRepository, SerializerInterface $serializer, EntityManagerInterface $em): JsonResponse 
    {

        $array_request = json_decode($request->getContent(), true);
        $votant = $membreRepository->findOneById($array_request['membre']);
        $semaine = $semaineRepository->find($array_request['id_semaine']);

        $avote = new AVote();
        $avote->setVotant($votant);
        $avote->setSemaine($semaine);

        $em->persist($avote);
        $em->flush();

        $jsonAVote = $serializer->serialize($avote, 'json');
        return new JsonResponse($jsonAVote, Response::HTTP_CREATED, [], true);
   }

}
