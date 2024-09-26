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
            

            // Utilisation de l'API OpenAI pour obtenir des suggestions de films avec Will Smith
            $message_user = "Je veux regarder un films avec des super héros marvel.";
            $message_assistant = "[{\"titre_film\": \"Iron Man\", \"sortie_film\": \"2008\", \"imdb_film\": \"https://www.imdb.com/title/tt0371746/\" }, {\"titre_film\": \"The Dark Knight\", \"sortie_film\": \"2008\", \"imdb_film\": \"https://www.imdb.com/title/tt0468569/\" }, {\"titre_film\": \"Inception\", \"sortie_film\": \"2010\", \"imdb_film\": \"https://www.imdb.com/title/tt1375666/\" }, {\"titre_film\": \"Interstellar\", \"sortie_film\": \"2014\", \"imdb_film\": \"https://www.imdb.com/title/tt0816692/\" }, {\"titre_film\": \"Tenet\", \"sortie_film\": \"2020\", \"imdb_film\": \"https://www.imdb.com/title/tt6723592/\" }]";
            $message_system = "Tu es un assistant qui a pour but de me proposer une liste de éxactement 5 films pas plus pas moins, ta réponse doit être au format JSON qui a la structure suivante : [{\"titre_film\": <Titre du film>, \"sortie_film\": <Année de sortie>, \"imdb_film\": <Lien IMDb du film> }].";
            $result = $client->chat()->create([
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    ['role' => 'user', 'content' => $message_user],
                    ['role' => 'assistant', 'content' => $message_assistant],
                    ['role' => 'system', 'content' => $message_system],
                ],
                'response_format' => ['type' => 'json_object']
            ]);

            $json_response_films = $result->choices[0]->message->content;
            $json_response_array = json_decode($json_response_films, true);

            echo "<pre> Tableaux";
            
            print_r($json_response_array);
            echo "</pre>";


            // Parcourir le tableau de films
            foreach ($json_response_array['films'] as $filmDataArray) {
                    // Capturer les informations dans des variables

                    // echo "<pre> film";
                    // print_r($filmDataArray);
                    // echo "</pre>";
                    
                    $titre_film = $filmDataArray['titre_film'];
                    $sortie_film = $filmDataArray['sortie_film'];
                    $lien_imdb = $filmDataArray['imdb_film'];

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
            }

            // Flush des changements en base de données
            $em->flush();

            // Renvoyer une réponse indiquant que les films et propositions ont été enregistrés
            return new JsonResponse(['message' => 'Les films et propositions ont été enregistrés en base de données'], Response::HTTP_CREATED);
        }
}
