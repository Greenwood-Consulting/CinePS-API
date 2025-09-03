<?php

namespace App\Entity;

use App\Entity\Semaine;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\PropositionRepository;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: PropositionRepository::class)]
class Proposition
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getPropositions", "filmsGagnants"])]
    private ?int $id = null;

    // TODO: attention,  un film peut potentiellement être associé à plusieurs propositions et dans ce cas cascade sur la suppression d'une proposition est dangereux
    #[ORM\ManyToOne(cascade: ['persist', 'remove'], inversedBy: 'propositions')]
    // Interdiction de supprimer un Film tant qu’il est encore référencé par au moins une Proposition.
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    #[Groups(["getPropositions"])]
    private ?Film $film = null;

    #[ORM\Column]
    #[Groups(["getPropositions", "filmsGagnants"])]
    private ?int $score = null;

    #[ORM\ManyToOne(inversedBy: 'propositions')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(["filmsGagnants"])]
    private ?Semaine $semaine = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: false)]
    #[Groups(["getPropositions", "filmsGagnants"])]
    private ?\DateTimeInterface $date = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFilm(): ?Film
    {
        return $this->film;
    }

    public function setFilm(?Film $film): self
    {
        $this->film = $film;

        return $this;
    }

    public function getScore(): ?int
    {
        return $this->score;
    }

    public function setScore(int $score): self
    {
        $this->score = $score;

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

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }
}
