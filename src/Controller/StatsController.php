<?php

namespace App\Controller;

use App\Entity\Semaine;
use App\Repository\MembreRepository;
use App\Repository\SemaineRepository;
use Symfony\Component\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class StatsController extends AbstractController
{
    // @TODO : merger tous ces endpoints en un seul endpoint car ils partent tous du proposeur en réalité

    //Récupère le nombre de proposeur 
    #[Route('/api/getNbPropositionsParProposeur', name: 'app_get_nbproposeur')]
    public function getCountProposeurSemaine(EntityManagerInterface $entityManager, SerializerInterface $serializer): JsonResponse
    {
        $queryBuilder_get_nb_propositions_par_proposeur = $entityManager->createQueryBuilder();
        $queryBuilder_get_nb_propositions_par_proposeur->select('p.Nom AS proposeur','COUNT(s.id) AS nb_semaines')
        ->from(Semaine::class, 's')
        ->where("s.type != 'PSSansFilm' ")
        ->andWhere("s.type != 'PasDePS' ")
        ->innerJoin('s.proposeur' ,'p')
        ->groupBy('s.proposeur');
        
        $Resultat_nb_propositions_par_proposeur = $queryBuilder_get_nb_propositions_par_proposeur->getQuery()->getResult();
        $jsonResultatNbPropositionParProposeur = $serializer->serialize($Resultat_nb_propositions_par_proposeur, 'json');
        
        if(isset($jsonResultatNbPropositionParProposeur))
        return new JsonResponse ($jsonResultatNbPropositionParProposeur, Response::HTTP_OK, [], true);
    }

    #[Route('/api/usersSatisfaction', name: 'app_users_satisfaction')]
    public function getUsersSatisfaction(EntityManagerInterface $entityManager, MembreRepository $membreRepository, SerializerInterface $serializer): JsonResponse
    {
        $users = $membreRepository->findAll();

        $usersSatisfaction = [];
        foreach ($users as $user) {
            $satisfactionVote = $user->getSatisfactionVotes($entityManager);
            $usersSatisfaction[] = [
            'user' => $user,
            'satisfactionVote' => $satisfactionVote,
            ];
        }

        usort($usersSatisfaction, function ($a, $b) {
            return $a['satisfactionVote'] <=> $b['satisfactionVote'];
        });

        $jsonUsersSatisfaction = $serializer->serialize($usersSatisfaction, 'json');

        return new JsonResponse($jsonUsersSatisfaction, Response::HTTP_OK, [], true);
    }

    #[Route('/api/usersNotesMoyennes', name: 'app_users_notes_moyennes')]
    public function getNotesMoyennesParMembre(EntityManagerInterface $entityManager, MembreRepository $membreRepository, SerializerInterface $serializer): JsonResponse
    {
        $users = $membreRepository->findAll();

        $usersNotesMoyennes = [];
        foreach ($users as $user) {
            $noteMoyenne = $user->getNoteMoyenne($entityManager);
            $usersNotesMoyennes[] = [
            'user' => $user,
            'noteMoyenne' => $noteMoyenne,
            'nbNotes' => $user->getNbNotes($entityManager),
            ];
        }
        // On enlève les membres qui n'ont pas donné de note
        $usersNotesMoyennes = array_filter($usersNotesMoyennes, function ($userNote) {
            return $userNote['noteMoyenne'] !== null;
        });
        // On fait un classement par ordre décroissant
        usort($usersNotesMoyennes, function ($a, $b) {
            return $a['noteMoyenne'] <=> $b['noteMoyenne'];
        });

        $jsonUsersNotesMoyennes = $serializer->serialize($usersNotesMoyennes, 'json');

        return new JsonResponse($jsonUsersNotesMoyennes, Response::HTTP_OK, [], true);
    }

}
