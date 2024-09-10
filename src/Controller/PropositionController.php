<?php

namespace App\Controller;

use DateTime;
use App\Entity\Film;
use App\Entity\Semaine;
use App\Entity\Proposition;
use App\Service\CurrentSemaine;
use App\Repository\SemaineRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PropositionController extends AbstractController
{

    // Crée une nouvelle proposition et le film associé
    #[Route('/api/proposition', name: 'createProposition', methods: ['POST'])]
    public function createProposition(Request $request, CurrentSemaine $currentSemaine, SemaineRepository $semaineRepository, SerializerInterface $serializer, EntityManagerInterface $em): JsonResponse
    {
        $array_request = json_decode($request->getContent(), true);

        $film = new Film();
        $film->setTitre($array_request['titre_film']);
        $film->setDate(new DateTime());
        $film->setSortieFilm($array_request['sortie_film']);
        $film->setImdb($array_request['imdb_film'] );

        $proposition = new Proposition();
        $proposition->setSemaine($currentSemaine->getCurrentSemaine($semaineRepository));
        $proposition->setFilm($film);
        $proposition->setScore(36);

        $em->persist($film);
        $em->persist($proposition);
        $em->flush();

        $jsonProposition = $serializer->serialize($proposition, 'json', ['groups' => 'getPropositions']); 
        return new JsonResponse($jsonProposition, Response::HTTP_CREATED, [], true);
    }


    #[Route('/api/PropositionPerdante/{proposeur_id}', name: 'proposition_perdante')]
    public function getPropositionPerdante(CurrentSemaine $currentSemaine, $proposeur_id, SemaineRepository $semaineRepository, EntityManagerInterface $entityManager, SerializerInterface $serializer): JsonResponse
    {
        $proposition_perdante = [];

        // Récupérer toutes les semaines pour le proposeur donné
        $semaines = $semaineRepository->findBy(['proposeur' => $proposeur_id]);

        // Récupérer la semaine courante
        $semaine_courante = $currentSemaine->getCurrentSemaine($semaineRepository);

        $all_propositions = [];
        $film_titres = []; // Pour stocker les titres des films déjà ajoutés

        foreach ($semaines as $semaine) {
            $id_semaine = $semaine->getId();

            // Sous-requête pour obtenir le score le plus élevé
            $subQuery = $entityManager->createQueryBuilder()
                ->select('MAX(p2.score)')
                ->from(Proposition::class, 'p2')
                ->leftJoin('p2.semaine', 's2')
                ->where('s2.id = :id')
                ->setParameter('id', $id_semaine);

            // Requête principale pour récupérer les propositions sauf celle avec le score le plus élevé
            $queryBuilder_get_proposition = $entityManager->createQueryBuilder();
            $queryBuilder_get_proposition->select('p')
                ->from(Proposition::class, 'p')
                ->leftJoin('p.semaine', 's')
                ->where('s.id = :id')
                ->andWhere($queryBuilder_get_proposition->expr()->lt('p.score', '(' . $subQuery->getDQL() . ')'))
                ->setParameter('id', $id_semaine);

            $get_proposition = $queryBuilder_get_proposition->getQuery()->getResult();

            // Ajouter les propositions à la liste globale
            $all_propositions = array_merge($all_propositions, $get_proposition);
        }

        // Mélanger les propositions et en récupérer 5 aléatoirement
        shuffle($all_propositions);
        $random_propositions = array_slice($all_propositions, 0, 5);

        foreach ($random_propositions as $proposition_existante) {
            // Récupérer l'objet Film associé à la proposition existante
            $film_existante = $proposition_existante->getFilm();

            // Vérifier si le film existe déjà dans la liste des titres ajoutés
            if (!in_array($film_existante->getTitre(), $film_titres)) {
                // Ajouter le titre du film à la liste des films déjà ajoutés
                $film_titres[] = $film_existante->getTitre();

                // Créer une nouvelle instance de Film avec les mêmes données
                $new_film = new Film();
                $new_film->setTitre($film_existante->getTitre());
                $new_film->setDate(new \DateTime()); // Par exemple, la date d'aujourd'hui
                $new_film->setSortieFilm($film_existante->getSortieFilm());
                $new_film->setImdb($film_existante->getImdb());

                $entityManager->persist($new_film);

                // Créer une nouvelle instance de Proposition en clonant les données de l'existante
                $new_proposition = new Proposition();
                $new_proposition->setSemaine($semaine_courante);
                $new_proposition->setFilm($new_film);
                $new_proposition->setScore(36);

                $entityManager->persist($new_proposition);

                // Ajouter à la liste des propositions perdantes pour la réponse
                $proposition_perdante[] = $new_proposition;
            }
        }

        // Modifier la propriété propositionTermine de la semaine courante
        $semaine_courante->setPropositionTermine(true);

        // Sauvegarder les nouvelles propositions, films, et la mise à jour de la semaine dans la base de données
        $entityManager->persist($semaine_courante);
        $entityManager->flush();

        // Sérialiser les résultats et retourner en JSON
        $jsonProposition = $serializer->serialize($proposition_perdante, 'json', ['groups' => 'getPropositions']);

        return new JsonResponse($jsonProposition, Response::HTTP_OK, [], true);
    }



}
