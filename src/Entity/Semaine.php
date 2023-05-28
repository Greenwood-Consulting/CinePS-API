<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\SemaineRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: SemaineRepository::class)]
class Semaine
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getPropositions"])]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups(["getPropositions"])]
    private ?\DateTimeInterface $jour = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getPropositions"])]
    private ?string $proposeur = null;

    #[ORM\Column]
    #[Groups(["getPropositions"])]
    private ?bool $proposition_termine = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getPropositions"])]
    private ?string $theme = null;

    #[ORM\OneToMany(mappedBy: 'semaine', targetEntity: Proposition::class, orphanRemoval: true)]
    #[Groups(["getPropositions"])]
    private Collection $propositions;

    public function __construct()
    {
        $this->propositions = new ArrayCollection();
    }

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

    /**
     * @return Collection<int, Proposition>
     */
    public function getPropositions(): Collection
    {
        return $this->propositions;
    }

    public function addProposition(Proposition $proposition): self
    {
        if (!$this->propositions->contains($proposition)) {
            $this->propositions->add($proposition);
            $proposition->setSemaine($this);
        }

        return $this;
    }

    public function removeProposition(Proposition $proposition): self
    {
        if ($this->propositions->removeElement($proposition)) {
            // set the owning side to null (unless already changed)
            if ($proposition->getSemaine() === $this) {
                $proposition->setSemaine(null);
            }
        }

        return $this;
    }
}
