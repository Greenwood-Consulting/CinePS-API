<?php

namespace App\Controller;

use DateTime;
use App\Entity\Film;
use App\Entity\Semaine;
use App\Entity\Proposition;
use Psr\Log\LoggerInterface;
use App\Service\CurrentSemaine;
use App\Repository\FilmRepository;
use App\Repository\SemaineRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\PropositionRepository;
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
    public function getPropositionPerdante($proposeur_id, Request $request, SemaineRepository $semaineRepository, EntityManagerInterface $entityManager, SerializerInterface $serializer): JsonResponse
    {
        $proposition_perdante = [];

    // Récupérer toutes les semaines pour le proposeur donné
    $semaines = $semaineRepository->findBy(['proposeur' => $proposeur_id]);

    foreach ($semaines as $semaine) {
        $id_semaine = $semaine->getId();

        // Sous-requête pour obtenir le score le plus élevé
        $subQuery = $entityManager->createQueryBuilder()
            ->select('MAX(p2.score)')
            ->from(Proposition::class, 'p2')
            ->leftJoin('p2.semaine', 's2')
            ->where('s2.id = :id');

        // Requête principale pour récupérer les films sauf celui avec le score le plus élevé
        $queryBuilder_get_proposition = $entityManager->createQueryBuilder();
        $queryBuilder_get_proposition->select('p')
            ->from(Proposition::class, 'p')
            ->leftJoin('p.semaine', 's')
            ->where('s.id = :id')
            ->andWhere('p.score < (' . $subQuery->getDQL() . ')')
            ->setParameter('id', $id_semaine);

        $get_proposition = $queryBuilder_get_proposition->getQuery()->getResult();

        if (empty($get_proposition)) {
            $titre_film = $request->request->get('titre_film');
            $sortie_film = $request->request->get('sortie_film');
            $lien_imdb = $request->request->get('imdb_film');

            // Si le champ titre_film est null ou vide, passer à l'itération suivante
            if ($titre_film === null || $titre_film === '') {
                continue;
            }

            // Créer un nouvel objet Film
            $film = new Film();
            $film->setTitre($titre_film);
            $film->setDate(new \DateTime());
            $film->setSortieFilm($sortie_film);
            $film->setImdb($lien_imdb);
            
            // Créer une nouvelle proposition
            $proposition = new Proposition();
            $proposition->setSemaine($semaine);
            $proposition->setFilm($film);
            $proposition->setScore(36);
            
            $entityManager->persist($film);
            $entityManager->persist($proposition);
            $entityManager->flush();
            
            // Ajouter la nouvelle proposition à $proposition_perdante
            $proposition_perdante[] = $proposition;
        } else {
            // Mélanger les propositions existantes et les ajouter à $proposition_perdante
            shuffle($get_proposition);
            $proposition_perdante = array_merge($proposition_perdante, $get_proposition);
        }
    }

    // Mélanger toutes les propositions perdantes et en retourner 5 aléatoirement
    shuffle($proposition_perdante);
    $random_proposition_perdante = array_slice($proposition_perdante, 0, 5);

    $jsonProposition = $serializer->serialize($random_proposition_perdante, 'json', ['groups' => 'getPropositions']);

    return new JsonResponse($jsonProposition, Response::HTTP_OK, [], true);
    }

}
