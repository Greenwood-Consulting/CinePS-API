<?php
namespace App\Service;

use DateTime;
use App\Entity\Semaine;
use Doctrine\ORM\EntityManagerInterface;

class CurrentSemaine
{

    public function getIdCurrentSemaine(EntityManagerInterface $entityManager): int
    {
        // Date du jour
        $curdate=new DateTime();

        // calcul de la date de fin de la période de vote
        $fin_periode_vote = new DateTime("Fri 14:00");
        $fin_periode_vote = $fin_periode_vote->format('Y-m-d H:i:s');

        // conversion de la date de fin en timestamp
        $deadline_vote = strtotime($fin_periode_vote);
        $deadline_vote = $deadline_vote*1000;

        // Get vendredi id_current_semaine
        if ($curdate->format('D')=="Fri"){ // Si nous sommes vendredi, alors id_current_semaine est défini par ce vendredi
            $friday_current_semaine = $curdate->format('Y-m-d');
        } else { // Sinon id_current_semaine est défini par vendredi prochain
            $friday_current_semaine = $curdate->modify('next friday')->format('Y-m-d');
        }

        //Récupère la propositionTerminé de id_semaine
        $queryBuilder_get_id_current_semaine = $entityManager->createQueryBuilder();
        $queryBuilder_get_id_current_semaine->select('s.id')
        ->from(Semaine::class, 's')
        ->where('s.jour = :jour')
        ->setParameter('jour', $friday_current_semaine);

        $result_current_semaine = $queryBuilder_get_id_current_semaine->getQuery()->getResult();

        $id_current_semaine = $result_current_semaine[0]['id'];

        if($result_current_semaine) {
            return $id_current_semaine;
        }
        return 0;
    }
}

?>