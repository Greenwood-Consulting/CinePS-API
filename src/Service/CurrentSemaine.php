<?php
namespace App\Service;

use DateTime;
use App\Entity\Semaine;
use App\Entity\AVote;
use App\Entity\Membre;
use Doctrine\ORM\EntityManagerInterface;

class CurrentSemaine
{

    private EntityManagerInterface $em;

    // Injection de la couche d'accès aux données
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function getFridayCurrentSemaine(): string
    {
        // Date du jour
        $curdate=new DateTime();

        // Get vendredi id_current_semaine
        if ($curdate->format('D')=="Fri"){ // Si nous sommes vendredi, alors id_current_semaine est défini par ce vendredi
            $friday_current_semaine = $curdate->format('Y-m-d');
        } else { // Sinon id_current_semaine est défini par vendredi prochain
            $friday_current_semaine = $curdate->modify('next friday')->format('Y-m-d');
        }

        return $friday_current_semaine;
    }


    public function getCurrentSemaine(): ?Semaine
    {
        $semaineRepository = $this->em->getRepository(Semaine::class);
        return $semaineRepository->findOneByJour(date_create($this->getFridayCurrentSemaine()));
    }


    // Recupère la semaine courante et calcule si les votes sont terminés
    public function getCurrentSemaineAndMetadata(): ?Semaine
    {
        $currentSemaine = $this->getCurrentSemaine();
        $currentSemaine->setIsVoteTermine($this->isVoteTermine());
        return $currentSemaine;
    }

    // L'état "isVoteTermine" depend des votes mais aussi des membres actifs
    // Il est plus facile de recaluler cette valeur a la demande plutoôt que de la stocker et de la maintenir à jour en base
    private function isVoteTermine(): ?bool
    {
        $currentSemaine = $this->getCurrentSemaine();

        if (!$currentSemaine) {
            return null; // Semaine non trouvée
        }   

        // Vérifie si la plage temporelle de vote est terminée
        if ($currentSemaine->hasVoteDeadlinePassed()) {
            return true;
        }

        // Vérifie si les membres actifs ont tous votés
        $idCurrentSemaine = $currentSemaine->getId();

        $votantsCount = $this->em->createQueryBuilder()
        ->select('COUNT(a.votant)')
        ->from(AVote::class, 'a')
        ->where('a.semaine = :id')
        ->setParameter('id', $idCurrentSemaine)
        ->getQuery()
        ->getSingleScalarResult();

        $membreActifCount = $this->em->createQueryBuilder()
        ->select('COUNT(m.id)')
        ->from(Membre::class, 'm')
        ->where('m.actif = :actif')
        ->setParameter('actif', true)
        ->getQuery()
        ->getSingleScalarResult();

        // compare les ayant voté + le proposeur au nombre de membres actifs
        $vote_termine_cette_semaine = (($votantsCount + 1) === $membreActifCount);

        return $vote_termine_cette_semaine;
    }
}

?>