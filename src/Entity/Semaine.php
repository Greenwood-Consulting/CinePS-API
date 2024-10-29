<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\SemaineRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Config\TypeSemaine;

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

    #[ORM\Column]
    #[Groups(["getPropositions"])]
    private ?bool $proposition_termine = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getPropositions"])]
    private ?string $theme = null;

    #[ORM\OneToMany(mappedBy: 'semaine', targetEntity: Proposition::class, orphanRemoval: true)]
    #[Groups(["getPropositions"])]
    private Collection $propositions;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(["getPropositions"])]
    private ?Membre $proposeur = null;

    #[ORM\OneToMany(mappedBy: 'semaine', targetEntity: AVote::class, orphanRemoval: true)]
    #[Groups(["getPropositions"])]
    private Collection $votants;

    #[Groups(["getPropositions"])]
    #[ORM\Column(length: 255)]
    private ?string $type = null;

    #[Groups(["getPropositions"])]
    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?Proposition $propositionGagnante = null;

    public function __construct()
    {
        $this->propositions = new ArrayCollection();
        $this->proposeur = new Membre();
        $this->votants = new ArrayCollection();
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

    public function getProposeur(): ?Membre
    {
        return $this->proposeur;
    }

    public function setProposeur(?Membre $proposeur): self
    {
        $this->proposeur = $proposeur;

        return $this;
    }

    /**
     * @return Collection<int, AVote>
     */
    public function getVotants(): Collection
    {
        return $this->votants;
    }

    public function addVotant(AVote $votant): self
    {
        if (!$this->votants->contains($votant)) {
            $this->votants->add($votant);
            $votant->setSemaine($this);
        }

        return $this;
    }

    public function removeVotant(AVote $votant): self
    {
        if ($this->votants->removeElement($votant)) {
            // set the owning side to null (unless already changed)
            if ($votant->getSemaine() === $this) {
                $votant->setSemaine(null);
            }
        }

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getPropositionGagnante(): ?Proposition
    {
        return $this->propositionGagnante;
    }

    public function setPropositionGagnante(?Proposition $propositionGagnante): self
    {
        $this->propositionGagnante = $propositionGagnante;

        return $this;
    }
}
