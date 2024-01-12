<?php

namespace App\Controller;

use App\Entity\Vote;
use App\Entity\AVote;
use App\Entity\Proposition;
use App\Service\CurrentSemaine;
use App\Repository\MembreRepository;
use App\Repository\SemaineRepository;
use JMS\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\PropositionRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class VoteController extends AbstractController
{
    // Retourner le  ou les film victorieux de la semaine id_semaine 
    //Il y a un tableau car il peut y avoir plusieurs films à égalité
    #[Route('/api/filmVictorieux/{id_semaine}', name:'FilmVictorieux', methods: ['GET'])]
    public function filmVictorieux(int $id_semaine, EntityManagerInterface $entityManager, SerializerInterface $serializer): JsonResponse
    {
        // Récupérer le film de la semaine qui a le score le plus élevé
        $queryBuilder_get_film_victorieux = $entityManager->createQueryBuilder();
        $queryBuilder_get_film_victorieux->select('p')
        ->from(Proposition::class, 'p')
        ->orderBy('p.score', 'DESC')
        ->setMaxResults(1)
        ->where('p.semaine = :semaine')
        ->setParameter('semaine', $id_semaine);

        $film_victorieux = $queryBuilder_get_film_victorieux->getQuery()->getResult();


        // Récupérer tous les films avec le même score dans la même semaine
        $queryBuilder_films_egalite = $entityManager->createQueryBuilder();
        $queryBuilder_films_egalite->select('p')
            ->from(Proposition::class, 'p')
            ->where('p.score = :score')
            ->andWhere('p.semaine = :semaine')
            ->setParameter('score', $film_victorieux[0]->getScore())
            ->setParameter('semaine', $id_semaine);

        $filmsAvecMemeScore = $queryBuilder_films_egalite->getQuery()->getResult();

        if (count($filmsAvecMemeScore) > 1) {
            $jsonfilmsAvecMemeScore = $serializer->serialize($filmsAvecMemeScore, 'json');
            return new JsonResponse($jsonfilmsAvecMemeScore, Response::HTTP_OK, [], true);
        }else {
            $jsonFilmVictorieux = $serializer->serialize($film_victorieux, 'json');
            return new JsonResponse($jsonFilmVictorieux, Response::HTTP_OK, [], true);
        
        }
    }

    // Enregistre une nouvelle ligne dans la table 'AVote'
    #[Route('/api/avote/{id_membre}', name:"aVote", methods: ['POST'])]
    public function avote(int $id_membre, CurrentSemaine $currentSemaine, SemaineRepository $semaineRepository, MembreRepository $membreRepository, SerializerInterface $serializer, EntityManagerInterface $em): JsonResponse 
    {
        $votant = $membreRepository->findOneById($id_membre);

        $avote = new AVote();
        $avote->setVotant($votant);
        $avote->setSemaine($currentSemaine->getCurrentSemaine($semaineRepository));

        $em->persist($avote);
        $em->flush();

        $jsonAVote = $serializer->serialize($avote, 'json');
        return new JsonResponse($jsonAVote, Response::HTTP_CREATED, [], true);
   }

    // Enregistre le vote et met à jour le score de la proposition
    #[Route('/api/saveVoteProposition', name:"saveVoteProposition", methods: ['POST'])]
    public function saveVoteProposition(Request $request, CurrentSemaine $currentSemaine, SemaineRepository $semaineRepository, PropositionRepository $propositionRepository, MembreRepository $membreRepository, SerializerInterface $serializer, EntityManagerInterface $em): JsonResponse 
    {
        $array_request = json_decode($request->getContent(), true);
        $membre = $membreRepository->findOneById($array_request['membre']);
        $proposition = $propositionRepository->findOneById($array_request['proposition']);
        $proposition->setScore($proposition->getScore() - $array_request['vote']);

        $vote = new Vote();
        $vote->setSemaine($currentSemaine->getCurrentSemaine($semaineRepository));
        $vote->setMembre($membre);
        $vote->setProposition($proposition);
        $vote->setVote($array_request['vote']);

        $em->persist($vote);
        $em->flush();

        $jsonVote = $serializer->serialize($vote, 'json'); 
        return new JsonResponse($jsonVote, Response::HTTP_CREATED, [], true);
    }
}
