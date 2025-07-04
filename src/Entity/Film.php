<?php

namespace App\Entity;

use App\Entity\Note;
use App\Entity\Proposition;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\FilmRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: FilmRepository::class)]
class Film
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getPropositions", "filmsGagnants"])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le titre du film est obligatoire")]
    #[Assert\Length(min: 1, max: 255, minMessage: "Le titre doit faire au moins {{ limit }} caractères", maxMessage: "Le titre ne peut pas faire plus de {{ limit }} caractères")]
    #[Groups(["getPropositions", "filmsGagnants"])]
    private ?string $titre = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups(["getPropositions", "filmsGagnants"])]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column]
    #[Groups(["getPropositions", "filmsGagnants"])]
    private ?int $sortie_film = null;

    #[ORM\Column(length: 600)]
    #[Groups(["getPropositions", "filmsGagnants"])]
    private ?string $imdb = null;

    #[ORM\OneToMany(mappedBy: 'film', targetEntity: Proposition::class)]
    #[Groups(["filmsGagnants"])]
    private Collection $propositions;

    #[ORM\OneToMany(mappedBy: 'film', targetEntity: Note::class)]
    #[Groups(["filmsGagnants"])]
    private Collection $notes;

    #[Groups(["filmsGagnants"])]
    private ?float $moyenne = null;

    #[Groups(["filmsGagnants"])]
    private ?float $ecartType = null;

    public function __construct()
    {
        $this->propositions = new ArrayCollection();
        $this->notes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): self
    {
        $this->titre = $titre;

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

    public function getSortieFilm(): ?int
    {
        return $this->sortie_film;
    }

    public function setSortieFilm(int $sortie_film): self
    {
        $this->sortie_film = $sortie_film;

        return $this;
    }

    public function getImdb(): ?string
    {
        return $this->imdb;
    }

    public function setImdb(string $imdb): self
    {
        $this->imdb = $imdb;

        return $this;
    }

    /**
     * @return Collection<int, Proposition>
     */
    public function getPropositions(): Collection
    {
        return $this->propositions;
    }

    /**
     * @return Collection<int, Note>
     */
    public function getNotes(): Collection
    {
        return $this->notes;
    }

    public function getMoyenne(): ?float
    {
        $notes = $this->getNotes();
        $total = 0;
        $count = count($notes);

        if ($count > 0) {
            foreach ($notes as $note) {
                $noteValue = $note->getNote();
                if ($noteValue !== null) {
                    $total += $noteValue;
                } else { // Si une note est null, alors cela correspond à une abstention, qui ne doit pas être prise en compte dans le calcul de la moyenne
                    $count--;
                }
            }
            $this->moyenne = $count > 0 ? $total / $count : null;
        } else {
            $this->moyenne = null;
        }
        return $this->moyenne;
    }

    public function getEcartType(): ?float
    {
        // Extraction des notes
        $notes = array_map(fn($n) => $n->getNote(), $this->getNotes()->toArray());
        // Filtrage des null: Si une note est null, alors cela correspond à une abstention, qui ne doit pas être prise en compte dans le calcul de la moyenne
        $notes = array_filter($notes, fn($n) => !is_null($n));

        // Exiger au moins 2 notes pour faire ce calcul
        $notesCount = count($notes);
        if($notesCount < 2) {
            return null;
        }
            
        $moyenne = $this->getMoyenne();
        if(is_null($moyenne)) {
            return null;
        }

        $sommeCarres = 0.0;
        foreach ($notes as $note) {
            $sommeCarres += pow($note - $moyenne, 2);
        }

        return round(sqrt($sommeCarres / $notesCount), 1);
    }

}
