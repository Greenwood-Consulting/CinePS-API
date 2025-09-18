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
    #[Groups(["avote:read"])]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(["getPropositions"])]
    private ?Membre $votant = null;

    #[ORM\ManyToOne(inversedBy: 'votants')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Semaine $semaine = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getSemaine(): ?Semaine
    {
        return $this->semaine;
    }

    public function setSemaine(?Semaine $semaine): self
    {
        $this->semaine = $semaine;

        return $this;
    }
}
