<?php
namespace App\Service;

use App\Entity\Proposition;
use App\Repository\SemaineRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class FilmVictorieux
{

    public function getFilmVictorieux(int $id_semaine, SemaineRepository $semaineRepository, EntityManagerInterface $entityManager, SerializerInterface $serializer): array
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
        $jsonFilmVictorieux = $serializer->serialize($film_victorieux, 'json', ['groups' => 'getPropositions']);
        $arrayFilmVictorieux = json_decode($jsonFilmVictorieux, true);

        if (count($arrayFilmVictorieux) === 0){
            return array();
        }

        // Récupérer tous les films avec le même score dans la même semaine
        $queryBuilder_films_egalite = $entityManager->createQueryBuilder();
        $queryBuilder_films_egalite->select('p')
            ->from(Proposition::class, 'p')
            ->where('p.score = :score')
            ->andWhere('p.semaine = :semaine')
            ->setParameter('score', $arrayFilmVictorieux[0]['score'])
            ->setParameter('semaine', $id_semaine);

        $filmsAvecMemeScore = $queryBuilder_films_egalite->getQuery()->getResult();

        $jsonfilmsAvecMemeScore = $serializer->serialize($filmsAvecMemeScore, 'json', ['groups' => 'getPropositions']);
        $arrayFilmsAvecMemeScore =  json_decode($jsonfilmsAvecMemeScore, true);

        $film_victorieux = $arrayFilmsAvecMemeScore;

        $proposition_film_victorieux = json_encode($film_victorieux, true);
        return $film_victorieux;
    }
}

?>