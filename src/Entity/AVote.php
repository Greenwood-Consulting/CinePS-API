<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\AVoteRepository;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: AVoteRepository::class)]
class AVote
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Semaine $semaine = null;

    #[ORM\ManyToOne(inversedBy: 'no')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(["getPropositions"])]
    private ?Membre $votant = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSemaine(): ?Semaine
    {
        return $this->semaine;
    }

    public function setSemaine(Semaine $semaine): self
    {
        $this->semaine = $semaine;

        return $this;
    }

    public function getVotant(): ?Membre
    {
        return $this->votant;
    }

    public function setVotant(?Membre $votant): self
    {
        $this->votant = $votant;

        return $this;
    }
}
