<?php

namespace App\Controller;

use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Annotation\Model;
use DateTime;
use App\Entity\Film;
use App\Entity\Note;
use App\Entity\Vote;
use App\Entity\Semaine;
use App\Service\CurrentSemaine;
use App\Repository\FilmRepository;
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

class SemaineController extends AbstractController
{
    
    #[OA\Tag(name: 'Semaine')]
    #[OA\Get(
        path: '/api/currentSemaine',
        summary: 'Get the current week data',
        description: 'Returns the data of the current week based on the current date.',
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful response with current week data',
                content: new OA\JsonContent(ref: new Model(type: Semaine::class, groups: ['getPropositions']))
            ),
            new OA\Response(
                response: 404,
                description: 'Not Found',
                content: new OA\JsonContent(type: 'object', properties: [
                    new OA\Property(property: 'error', type: 'string', example: 'Not Found')
                ])
            )
        ]
    )]
    // Retourne les datas de la semaine en cours
    #[Route('/api/currentSemaine', name: 'currentSemaine', methods: ['GET'])]
    public function currentSemaine(CurrentSemaine $currentSemaineService, SerializerInterface $serializer): JsonResponse
    {
        // Recupère la semaine courante et calcule si les votes sont terminés
        $currentSemaine = $currentSemaineService->getCurrentSemaineAndMetadata();

        if($currentSemaine) {
            $jsonFilmProposes = $serializer->serialize($currentSemaine, 'json', ['groups' => 'getPropositions']);
            return new JsonResponse ($jsonFilmProposes, Response::HTTP_OK, [], true);
        } else {
            return new JsonResponse(["error" => "Not Found"], 404);
        }
    }

    #[OA\Tag(name: 'Semaine')]
    #[OA\Get(
        path: '/api/nextProposeurs/{id_semaine}',
        summary: 'Get the list of proposers for upcoming weeks',
        description: 'Returns the list of proposers for the weeks following the given week ID.',
        parameters: [
            new OA\Parameter(
                name: 'id_semaine',
                in: 'path',
                required: true,
                description: 'The ID of the current week to fetch proposers for subsequent weeks',
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful response with the list of proposers',
                content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: new Model(type: Semaine::class, groups: ['getPropositions'])))
            ),
            new OA\Response(
                response: 404,
                description: 'Not Found',
                content: new OA\JsonContent(type: 'object', properties: [
                    new OA\Property(property: 'error', type: 'string', example: 'Not Found')
                ])
            )
        ]
    )]
    // Retourne la liste des proposeurs des prochaines semaines
    #[Route('/api/nextProposeurs/{id_semaine}', name:'nextProposeurs', methods: ['GET'])]
    public function nextProposeurs(int $id_semaine, EntityManagerInterface $entityManager, SerializerInterface $serializer): JsonResponse
    {
        //Récuperer le jour de la semaine $id_semaine
        $queryBuilder_get_jour = $entityManager->createQueryBuilder();
        $queryBuilder_get_jour->select('s.jour')
        ->from(Semaine::class, 's')
        ->where('s.id = :id')
        ->setParameter('id', $id_semaine);

        $resultats_jour = $queryBuilder_get_jour->getQuery()->getResult();

        //Récuperer les proposeurs des semaines postérieurs au jour précédent récupéré

        $queryBuilder_get_proposeurs = $entityManager->createQueryBuilder();
        $queryBuilder_get_proposeurs->select('s')
        ->from(Semaine::class, 's')
        ->where('s.jour >= :jour')
        ->setParameter('jour', $resultats_jour[0]['jour']);

        $resultats_proposeurs = $queryBuilder_get_proposeurs->getQuery()->getResult();
        $jsonResultatsProposeurs = $serializer->serialize($resultats_proposeurs, 'json', ['groups' => 'getPropositions']);

        return new JsonResponse ($jsonResultatsProposeurs, Response::HTTP_OK, [], true);

    }

    #[OA\Tag(name: 'Semaine')]
    #[OA\Get(
        path: '/api/votes/{id_semaine}',
        summary: 'Get votes and ratings for a specific week',
        description: 'Returns the votes and ratings for all propositions of a specific week.',
        parameters: [
            new OA\Parameter(
                name: 'id_semaine',
                in: 'path',
                required: true,
                description: 'The ID of the week to fetch votes and ratings for',
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful response with votes and ratings',
                content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: new Model(type: Semaine::class, groups: ['getPropositions'])))
            ),
            new OA\Response(
                response: 404,
                description: 'Not Found',
                content: new OA\JsonContent(type: 'object', properties: [
                    new OA\Property(property: 'error', type: 'string', example: 'Not Found')
                ])
            )
        ]
    )]
    // Votes de la semaine
    #[Route('/api/votes/{id_semaine}', name:'votes', methods: ['GET'])]
    public function votes(int $id_semaine, MembreRepository $membreRepository, PropositionRepository $propositionRepository, EntityManagerInterface $entityManager, SerializerInterface $serializer): JsonResponse
    {
        // Récupération des propositions de la semaine
        $propositions = $propositionRepository->findBySemaine($id_semaine);
        $jsonPropositions = $serializer->serialize($propositions, 'json', ['groups' => 'getPropositions']);
        $arrayPropositions = json_decode($jsonPropositions, true);

        $array_propositions_avec_votes_et_notes = array();
        foreach($arrayPropositions as $proposition){

            $membres = $membreRepository->findAll();
            $jsonMembres = $serializer->serialize($membres, 'json');
            $arrayMembres = json_decode($jsonMembres, true);

            $proposition_votes = array(); // tableau dans lequel on stocke les votes de cette proposition
            
            $proposition_notes = array(); // tableau dans lequel on stocke les notes de cette proposition
            foreach($arrayMembres as $membre){
                // Résupérer le vote de l'utilisateur pour cette proposition
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
                    $proposition_votes[] = array("membre" => $membre['Prenom'], "vote" => '');;
                } else {
                    $proposition_votes[] = array("membre" => $membre['Prenom'], "vote" => $arrayVote[0]['vote']);
                }

                // Récupérer la note de l'utilisateur pour ce film
                $queryBuilder_get_note = $entityManager->createQueryBuilder();
                $queryBuilder_get_note->select('n.note')
                ->from(Note::class, 'n')
                ->where('n.film = :id_film')
                ->andWhere('n.membre = :id_membre')
                ->setParameters(array('id_film' => $proposition['film']['id'], 'id_membre' => $membre['id']));
        
                $resultat_note = $queryBuilder_get_note->getQuery()->getResult();
                $jsonResultatNote = $serializer->serialize($resultat_note, 'json', ['groups' => 'getPropositions']);
                $arrayNote = json_decode($jsonResultatNote, true);

                if (empty($arrayNote)){
                    $proposition_notes[] = array("membre" => $membre['id'], "note" => '');;
                } else {
                    $proposition_notes[] = array("membre" => $membre['id'], "note" => $arrayNote[0]['note']);
                }
            }
            $proposition['vote'] = $proposition_votes;
            $proposition['note'] = $proposition_notes;
            $array_propositions_avec_votes_et_notes[] = $proposition;
        } // fin du parcours des propositions

        $jsonResultatsPropositiuonsAvecVotesEtNotes = $serializer->serialize($array_propositions_avec_votes_et_notes, 'json', ['groups' => 'getPropositions']);
        return new JsonResponse ($jsonResultatsPropositiuonsAvecVotesEtNotes, Response::HTTP_OK, [], true);

    }

    #[OA\Tag(name: 'Semaine')]
    #[OA\Patch(
        path: '/api/semaine/{id_semaine}',
        summary: 'Update a specific week',
        description: 'Updates the details of a specific week, including propositions, theme, winning proposition, and other attributes.',
        parameters: [
            new OA\Parameter(
                name: 'id_semaine',
                in: 'path',
                required: true,
                description: 'The ID of the week to update',
                schema: new OA\Schema(type: 'integer')
            )
        ],
        requestBody: new OA\RequestBody(
            description: 'Data to update the week',
            required: true,
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(property: 'proposition_terminee', type: 'boolean', description: 'Indicates if the propositions are finished'),
                    new OA\Property(property: 'theme', type: 'string', description: 'The theme of the week'),
                    new OA\Property(property: 'proposition_gagnante', type: 'integer', description: 'ID of the winning proposition'),
                    new OA\Property(property: 'proposeur_id', type: 'integer', description: 'ID of the proposer'),
                    new OA\Property(property: 'raison_changement_film', type: 'string', description: 'Reason for changing the film'),
                    new OA\Property(property: 'type_semaine', type: 'string', description: 'Type of the week (e.g., PSDroitDivin)'),
                    new OA\Property(property: 'droit_divin_titre_film', type: 'string', description: 'Title of the film for PSDroitDivin'),
                    new OA\Property(property: 'droit_divin_date_film', type: 'string', format: 'date', description: 'Release date of the film for PSDroitDivin'),
                    new OA\Property(property: 'droit_divin_lien_imdb', type: 'string', description: 'IMDB link of the film for PSDroitDivin')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful response with updated week data',
                content: new OA\JsonContent(ref: new Model(type: Semaine::class, groups: ['getPropositions']))
            ),
            new OA\Response(
                response: 404,
                description: 'Not Found',
                content: new OA\JsonContent(type: 'object', properties: [
                    new OA\Property(property: 'error', type: 'string', example: 'Not Found')
                ])
            )
        ]
    )]
    // Met à jour une semaine
    #[Route('/api/semaine/{id_semaine}', name: 'updateSemaine', methods: ['PATCH'])]
    public function createProposition($id_semaine, Request $request, SemaineRepository $semaineRepository, FilmRepository $filmRepository, PropositionRepository $propositionRepository, MembreRepository $membreRepository, SerializerInterface $serializer, EntityManagerInterface $em): JsonResponse
    {
        $array_request = json_decode($request->getContent(), true);

        $semaine = $semaineRepository->findOneById($id_semaine);

        if (isset($array_request['proposition_terminee'])){
            $semaine->setPropositionTermine($array_request['proposition_terminee']);
        }
        if (isset($array_request['theme'])){
            $semaine->setTheme($array_request['theme']);
        }
        if (isset($array_request['proposition_gagnante'])){
            $film = $filmRepository->findOneById($array_request['proposition_gagnante']);
            $semaine->setFilmVu($film);
        }
        if (isset($array_request['proposeur_id'])){
            $proposeur = $membreRepository->findOneById($array_request['proposeur_id']);
            $semaine->setProposeur($proposeur);
        }
        if (isset($array_request['raison_changement_film'])){
            $semaine->setRaisonPropositionChoisie($array_request['raison_changement_film']);
        }


        if (isset($array_request['type_semaine']) && $array_request['type_semaine'] === 'PSDroitDivin'){
            $semaine->setType('PSDroitDivin');


            $film = new Film();
            $film->setTitre($array_request['droit_divin_titre_film']);
            $film->setSortieFilm($array_request['droit_divin_date_film']);
            $film->setImdb($array_request['droit_divin_lien_imdb'] );
        
            $em->persist($film);

            $semaine->setFilmVu($film);
        }


        $em->persist($semaine);
        $em->flush();

        $jsonProposition = $serializer->serialize($semaine, 'json', ['groups' => 'getPropositions']); 
        return new JsonResponse($jsonProposition, Response::HTTP_OK, [], true);
    }

}
?>