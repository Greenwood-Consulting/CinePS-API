<?php

namespace App\Controller;

use OpenAI;
use DateTime;
use App\Entity\Film;
use App\Entity\Semaine;
use App\Entity\Proposition;
use App\Service\CurrentSemaine;
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

    #[Route('/api/Allproposition', name: 'app_Allproposition')]
    public function getAllProposition(PropositionRepository $propositionRepository, SerializerInterface $serializer): JsonResponse
    {

        $propositionList = $propositionRepository->findAll();

        $jsonPropositionList = $serializer->serialize($propositionList, 'json');
        return new JsonResponse($jsonPropositionList, Response::HTTP_OK, [], true);
    }

        #[Route('/api/propositionOpenAI', name: 'createPropositionApi', methods: ['POST'])]
        public function createPropositionOpenAI(Request $request, CurrentSemaine $currentSemaine, SemaineRepository $semaineRepository, EntityManagerInterface $em): JsonResponse
        {
            // Création et configuration du client OpenAI
            $client = OpenAI::client('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');

            // Utilisation de l'API OpenAI pour obtenir des suggestions de films avec Will Smith
            $message = "Peux-tu me proposer une liste de 5 films d'horreur, avec leur date de sortie et le lien IMDb ? Chaque film doit être présenté de cette manière : Titre du film (année de sortie) ## lien IMDb du film. Entre chaque film il faut ajouter un séparateur qui est : @@ . Ne mets pas de separateur après le dernier film.  Ne mets pas de tiret ou de numéros avant les titres de films.";
            $result = $client->chat()->create([
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    ['role' => 'user', 'content' => $message],
                ],
            ]);

            echo "<pre>";
            print_r($result->choices);
            echo "</pre>";

            // Initialisation du tableau de films
            $filmsData = "";

            // Diviser la chaîne de caractères pour obtenir les informations sur chaque film
            $arrayFilms = explode("@@", $result->choices[0]->message->content);
            echo "<pre>";
            print_r($arrayFilms);
            echo "</pre>";

            // Parcourir le tableau de films
            foreach ($arrayFilms as $filmDataString) {
                // Expression régulière pour capturer le titre du film, la date et le lien IMDb
                $pattern = "/ ([\s\S]*?) \((\d{4})\) ## (https:\/\/www\.imdb\.com\/title\/tt\d+\/)/";

                // Vérifie si la chaîne correspond au pattern
                if (preg_match($pattern, $filmDataString, $matches)) {
                    // Capturer les informations dans des variables
                    $titre_film = $matches[1];
                    $sortie_film = $matches[2];
                    $lien_imdb = $matches[3];

                    // Créer et configurer l'objet Film
                    $film = new Film();
                    $film->setTitre($titre_film);
                    $film->setImdb($lien_imdb);
                    $film->setSortieFilm((int)$sortie_film);
                    $film->setDate(new DateTime());

                    // Afficher les informations du film pour le débogage
                    echo "Titre: $titre_film, Sortie: $sortie_film, IMDb: $lien_imdb\n";

                    // Enregistrer le film en base de données
                    $em->persist($film);

                    // Créer et configurer l'objet Proposition
                    $proposition = new Proposition();
                    $proposition->setSemaine($currentSemaine->getCurrentSemaine($semaineRepository));
                    $proposition->setFilm($film);
                    $proposition->setScore(36);

                    // Afficher les informations de la proposition pour le débogage
                    echo "Proposition créée pour le film: $titre_film\n";

                    // Enregistrer la proposition en base de données
                    $em->persist($proposition);
                } else {
                    // Afficher un message d'erreur si le film n'est pas extrait correctement
                    echo "Erreur lors de l'extraction des informations du film: $filmDataString\n";
                }
            }

            // Flush des changements en base de données
            $em->flush();

            // Renvoyer une réponse indiquant que les films et propositions ont été enregistrés
            return new JsonResponse(['message' => 'Les films et propositions ont été enregistrés en base de données'], Response::HTTP_CREATED);
        }
}
