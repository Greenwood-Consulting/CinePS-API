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


    public function isVoteTermine(): ?bool
    {
        $currentSemaine = $this->getCurrentSemaine();

        if (!$currentSemaine) {
            return null; // Semaine non trouvée
        }   

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
        ->where('m.actif = 1')
        ->getQuery()
        ->getSingleScalarResult();

        // les ayants voté & le proposeur 
        $vote_termine_cette_semaine = (($votantsCount + 1) === $membreActifCount);

        return $vote_termine_cette_semaine;
    }
}

?>