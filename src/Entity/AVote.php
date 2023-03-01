<?php

namespace App\Entity;

use App\Repository\AVoteRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AVoteRepository::class)]
class AVote
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Membre $votant = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Semaine $Semaine = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVotant(): ?Membre
    {
        return $this->votant;
    }

    public function setVotant(Membre $votant): self
    {
        $this->votant = $votant;

        return $this;
    }

    public function getSemaine(): ?Semaine
    {
        return $this->Semaine;
    }

    public function setSemaine(Semaine $Semaine): self
    {
        $this->Semaine = $Semaine;

        return $this;
    }
}
