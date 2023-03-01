<?php

namespace App\Entity;

use App\Repository\SemaineRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SemaineRepository::class)]
class Semaine
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $jour = null;

    #[ORM\Column(length: 255)]
    private ?string $proposeur = null;

    #[ORM\Column]
    private ?bool $proposition_termine = null;

    #[ORM\Column(length: 255)]
    private ?string $theme = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getJour(): ?\DateTimeInterface
    {
        return $this->jour;
    }

    public function setJour(\DateTimeInterface $jour): self
    {
        $this->jour = $jour;

        return $this;
    }

    public function getProposeur(): ?string
    {
        return $this->proposeur;
    }

    public function setProposeur(string $proposeur): self
    {
        $this->proposeur = $proposeur;

        return $this;
    }

    public function isPropositionTermine(): ?bool
    {
        return $this->proposition_termine;
    }

    public function setPropositionTermine(bool $proposition_termine): self
    {
        $this->proposition_termine = $proposition_termine;

        return $this;
    }

    public function getTheme(): ?string
    {
        return $this->theme;
    }

    public function setTheme(string $theme): self
    {
        $this->theme = $theme;

        return $this;
    }
}
