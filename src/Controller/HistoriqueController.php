<?php

namespace App\Controller;

use App\Entity\Note;
use App\Entity\Vote;
use App\Entity\Semaine;
use App\Service\CurrentSemaine;
use App\Service\FilmVictorieux;
use App\Repository\MembreRepository;
use App\Repository\SemaineRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\PropositionRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HistoriqueController extends AbstractController
{
    #[Route('/api/historique', name: 'app_historique', methods: ['GET'])]
    public function historique(FilmVictorieux $filmVictorieux, PropositionRepository $propositionRepository, SemaineRepository $semaineRepository, MembreRepository $membreRepository, CurrentSemaine $currentSemaine, SerializerInterface $serializer, EntityManagerInterface $entityManager): JsonResponse
    {
        // Récupération de la liste des membres
        $membreList = $membreRepository->findAll();
        $jsonMembreList = $serializer->serialize($membreList, 'json', ['groups' => 'getPropositions']);
        $arrayMembreList = json_decode($jsonMembreList, true);

        //Récupère les semaines plus anciennes que $friday_current_semaine
        $friday_current_semaine = $currentSemaine->getFridayCurrentSemaine();

        $queryBuilder_get_id_current_semaine = $entityManager->createQueryBuilder();
        $queryBuilder_get_id_current_semaine->select('s')
        ->from(Semaine::class, 's')
        ->where('s.jour < :jour')
        ->orderBy('s.jour', 'DESC')
        ->setParameter('jour', $friday_current_semaine);

        $resulat_old_semaines = $queryBuilder_get_id_current_semaine->getQuery()->getResult();
        $jsonOldSemaines = $serializer->serialize($resulat_old_semaines, 'json', ['groups' => 'getPropositions']);
        $arrayOldSemaines = json_decode($jsonOldSemaines, true);

        // Construction de l'historique de toutes les vieilles semaines
        $array_historique = array();
        $array_historique['semaines'] = array();
        $array_historique['membres'] = $arrayMembreList;
        foreach($arrayOldSemaines as $semaine){
            // Récupération des propositions de la semaine
            $arrayPropositions = $semaine['propositions'];

            $array_propositions_avec_votes_et_notes = array();
            foreach($arrayPropositions as $proposition){

                $membres = $membreRepository->findAll();
                $jsonMembres = $serializer->serialize($membres, 'json');
                $arrayMembres = json_decode($jsonMembres, true);

                $proposition_votes = array(); // tableau dans lequel on stocke les votes de cette proposition
                $proposition_notes = array(); // tableau dans lequel on stocke les notes de cette proposition
                foreach($arrayMembres as $membre){
                    // Récupérer le vote de l'utilisateur pour cette proposition
                    $queryBuilder_get_vote = $entityManager->createQueryBuilder();
                    $queryBuilder_get_vote->select('v.vote')
                    ->from(Vote::class, 'v')
                    ->where('v.proposition = :id_proposition')
                    ->andWhere('v.membre = :id_membre')
                    ->setParameters(array('id_proposition' => $proposition['id'], 'id_membre' => $membre['id']));
            
                    $resultat_vote = $queryBuilder_get_vote->getQuery()->getResult();
                    $jsonResultatVote = $serializer->serialize($resultat_vote, 'json', ['groups' => 'getPropositions']);
                    $arrayVote = json_decode($jsonResultatVote, true);

                    if (empty($arrayVote)){
                        $proposition_votes[] = array("membre" => $membre['Prenom'], "vote" => '');
                    } else {
                        $proposition_votes[] = array("membre" => $membre['Prenom'], "vote" => $arrayVote[0]['vote']);
                    }

                    // Récupérer la note de l'utilisateur pour cette proposition
                    $queryBuilder_get_note = $entityManager->createQueryBuilder();
                    $queryBuilder_get_note->select('n.note')
                    ->from(Note::class, 'n')
                    ->where('n.proposition = :id_proposition')
                    ->andWhere('n.membre = :id_membre')
                    ->setParameters(array('id_proposition' => $proposition['id'], 'id_membre' => $membre['id']));
            
                    $resultat_note = $queryBuilder_get_note->getQuery()->getResult();
                    $jsonResultatNote = $serializer->serialize($resultat_note, 'json', ['groups' => 'getPropositions']);
                    $arrayNote = json_decode($jsonResultatNote, true);

                    if (empty($arrayNote)){
                        $proposition_notes[] = array("membre" => $membre['id'], "note" => '');
                    } else {
                        $proposition_notes[] = array("membre" => $membre['id'], "note" => $arrayNote[0]['note']);
                    }
                }
                $proposition['vote'] = $proposition_votes;
                $proposition['note'] = $proposition_notes;
                $array_propositions_avec_votes_et_notes[] = $proposition;
            } // fin du parcours des propositions

            $film_victorieux = $filmVictorieux->getFilmVictorieux($semaine['id'], $semaineRepository, $entityManager, $serializer);

            $semaine['propositions'] = $array_propositions_avec_votes_et_notes;
            $semaine['film_victorieux'] = $film_victorieux;
            $array_historique['semaines'][] = $semaine; 
        }

        
        if($array_historique) {
            $jsonProposition = $serializer->serialize($array_historique, 'json', ['groups' => 'getPropositions']);
            return new JsonResponse($jsonProposition, Response::HTTP_OK, [], true);
        }
        return new JsonResponse(["error" => "Not Found"], 404);

    }

    // WIP
    #[Route('/api/historique_proposeur/{id_membre}', name: 'app_historique_proposeur', methods: ['GET'])]
    public function historique_membre(int $id_membre): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/HistoriqueController.php',
        ]);
    }

}
