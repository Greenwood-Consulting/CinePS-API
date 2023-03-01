<?php

namespace App\Entity;

use App\Entity\Membre;
use App\Entity\Semaine;
use App\Entity\Proposition;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\VoteRepository;

#[ORM\Entity(repositoryClass: VoteRepository::class)]
class Vote
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Semaine $Semaine = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Membre $membre = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Proposition $proposition = null;

    #[ORM\Column]
    private ?int $vote = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getMembre(): ?Membre
    {
        return $this->membre;
    }

    public function setMembre(?Membre $membre): self
    {
        $this->membre = $membre;

        return $this;
    }

    public function getProposition(): ?Proposition
    {
        return $this->proposition;
    }

    public function setProposition(?Proposition $proposition): self
    {
        $this->proposition = $proposition;

        return $this;
    }

    public function getVote(): ?int
    {
        return $this->vote;
    }

    public function setVote(int $vote): self
    {
        $this->vote = $vote;

        return $this;
    }
}
