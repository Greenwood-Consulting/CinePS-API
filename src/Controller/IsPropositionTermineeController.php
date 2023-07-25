<?php

namespace App\Controller;

use App\Entity\AVote;
use App\Entity\Membre;
use App\Entity\Semaine;
use JMS\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class IsPropositionTermineeController extends AbstractController
{

    //Vérifie si la proposition_terminée = à 0 où 1
    #[Route('/api/isPropositionTerminee/{id_semaine}', name: 'app_is_proposition_terminee')]
    public function isPropositionTerminee(int $id_semaine, EntityManagerInterface $entityManager, SerializerInterface $serializer): JsonResponse
    {
        //Récupère la propositionTerminé de id_semaine
        $queryBuilder_get_jour = $entityManager->createQueryBuilder();
        $queryBuilder_get_jour->select('s.id, COALESCE(s.proposition_termine, 0) as proposition_termine')
        ->from(Semaine::class, 's')
        ->where('s.id = :id')
        ->setParameter('id', $id_semaine);


        $resultat_proposition_terminee = $queryBuilder_get_jour->getQuery()->getResult();

        if (empty($resultat_proposition_terminee)) {
            $resultat_controller = $serializer->serialize([0 => ['proposition_termine' => 0]], 'json');
        } else {
            $jsonResultatsPropositionTerminee = $serializer->serialize($resultat_proposition_terminee, 'json');
            $resultat_controller = $jsonResultatsPropositionTerminee;
        }


        return new JsonResponse ($resultat_controller, Response::HTTP_OK, [], true);
    }

    //Indique si le vote pour la semaine $id_semaine est terminée
    #[Route('/api/isVoteTermine/{id_semaine}', name: 'is_vote_termine')]
    public function isVoteTermineCetteSemaine(int $id_semaine, EntityManagerInterface $entityManager, SerializerInterface $serializer): JsonResponse
    {
        ///Récupère le nombre de votants de la semaine $id_semaine
        $queryBuilder_get_nb_votants_semaine = $entityManager->createQueryBuilder();
        $queryBuilder_get_nb_votants_semaine->select('COUNT(a.votant)')
        ->from(AVote::class, 'a')
        ->where('a.semaine = :id')
        ->setParameter('id', $id_semaine);

        $resultat_nb_votants_semaine_termine = $queryBuilder_get_nb_votants_semaine->getQuery()->getResult();
        $get_int_resultat_nb_votants_semaine_termine = $resultat_nb_votants_semaine_termine[0][1];


        //Récupere le nombre de membres - 1
        $queryBuilder_get_membre = $entityManager->createQueryBuilder();
        $queryBuilder_get_membre->select('COUNT(m.id)')
        ->from(Membre::class, 'm');

        $resultat_count_membre = $queryBuilder_get_membre->getQuery()->getResult();
        $get_int_resultat_count_membre = $resultat_count_membre[0][1];

        $vote_termine_cette_semaine = ($get_int_resultat_nb_votants_semaine_termine == ($get_int_resultat_count_membre -1));
        
        $jsonVote_termine_cette_semaine = $serializer->serialize($vote_termine_cette_semaine, 'json');

        return new JsonResponse ($jsonVote_termine_cette_semaine, Response::HTTP_OK, [], true);
    }
}
