<?php

namespace App\Entity;

use App\Repository\PreSelectionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PreSelectionRepository::class)]
class PreSelection
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['preselection:read'])]
    private ?int $id = null;

    // supprimer une pre-selection supprime en cascade les films associés
    #[ORM\ManyToMany(mappedBy: "preSelections", targetEntity: Film::class, cascade: ['persist', 'remove'])]
    #[Groups(['preselection:read'])]
    private Collection $films;

    #[ORM\ManyToOne(targetEntity: Membre::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank(message: "Le membre est obligatoire")]
    #[Groups(['preselection:read', 'preselection:write'])]
    private ?Membre $membre = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le thème est obligatoire")]
    #[Groups(['preselection:read', 'preselection:write'])]
    private ?string $theme = null;

    public function __construct()
    {
        $this->films = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, Film>
     */
    public function getFilms(): Collection
    {
        return $this->films;
    }

    public function addFilm(Film $film): self
    {
        if (!$this->films->contains($film)) {
            $this->films->add($film);
        }

        return $this;
    }

    public function removeFilm(Film $film): self
    {
        $this->films->removeElement($film);

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
