<?php

namespace App\Entity;

use App\Entity\Film;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\NoteRepository;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: NoteRepository::class)]
class Note
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    #[Groups(["filmsGagnants"])]
    private ?int $note = null;

    #[ORM\ManyToOne(inversedBy: 'notes')]
    // Interdiction de supprimer un Film tant qu’il est encore référencé par au moins une Note.
    #[ORM\JoinColumn(onDelete: 'RESTRICT')]
    private ?Film $film = null;

    #[ORM\ManyToOne]
    #[Groups(["filmsGagnants"])]
    private ?Membre $membre = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNote(): ?int
    {
        return $this->note;
    }

    public function setNote(?int $note): self
    {
        $this->note = $note;

        return $this;
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

    public function getMembre(): ?Membre
    {
        return $this->membre;
    }

    public function setMembre(?Membre $membre): self
    {
        $this->membre = $membre;

        return $this;
    }
}
