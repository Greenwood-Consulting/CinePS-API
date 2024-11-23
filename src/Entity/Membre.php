<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\MembreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: MembreRepository::class)]
class Membre
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getPropositions", "filmsGagnants"])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getPropositions", "filmsGagnants"])]
    private ?string $Nom = null;

    #[Groups(["getPropositions"])]
    #[ORM\Column(length: 255)]
    private ?string $Prenom = null;

    #[ORM\Column(length: 255)]
    private ?string $mail = null;

    #[ORM\Column(length: 255)]
    private ?string $mdp = null;

    #[ORM\Column]
    private ?bool $actif = null;

    #[ORM\ManyToOne(inversedBy: 'Membre')]
    #[ORM\JoinColumn(nullable: false)]

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->Nom;
    }

    public function setNom(string $Nom): self
    {
        $this->Nom = $Nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->Prenom;
    }

    public function setPrenom(string $Prenom): self
    {
        $this->Prenom = $Prenom;

        return $this;
    }

    public function getMail(): ?string
    {
        return $this->mail;
    }

    public function setMail(string $mail): self
    {
        $this->mail = $mail;

        return $this;
    }

    public function getMdp(): ?string
    {
        return $this->mdp;
    }

    public function setMdp(string $mdp): self
    {
        $this->mdp = $mdp;

        return $this;
    }

    public function isActif(): ?bool
    {
        return $this->actif;
    }

    public function setActif(bool $actif): static
    {
        $this->actif = $actif;

        return $this;
    }

    public function getSatisfactionVotes(EntityManagerInterface $entityManager): ?float
    {
        $query = $entityManager->createQuery(
            'SELECT v
            FROM App\Entity\Vote v
            JOIN v.proposition p
            JOIN p.semaine s
            WHERE (p.id = s.filmVu
            OR (s.filmVu IS NULL AND p.id = (
            SELECT MIN(p2.id)
            FROM App\Entity\Proposition p2
            WHERE p2.semaine = s AND p2.score = (
                SELECT MAX(p3.score)
                FROM App\Entity\Proposition p3
                WHERE p3.semaine = s
            )
            )))
            AND v.membre = :currentUser'
        )->setParameter('currentUser', $this->getId());

        $votes = $query->getResult();

        if (count($votes) === 0) {
            return null;
        }

        $totalVotes = 0;
        foreach ($votes as $vote) {
            $totalVotes += $vote->getVote();
        }

        return $totalVotes / count($votes);

    }

    public function getNoteMoyenne (EntityManagerInterface $entityManager): ?float
    {
        $query = $entityManager->createQuery(
            'SELECT n
            FROM App\Entity\Note n
            WHERE n.membre = :currentUser'
        )->setParameter('currentUser', $this->getId());

        $notes = $query->getResult();

        if (count($notes) === 0) {
            return null;
        }

        $totalNotes = 0;
        foreach ($notes as $note) {
            $totalNotes += $note->getNote();
        }

        return $totalNotes / count($notes);
    }

    public function getNbNotes(EntityManagerInterface $entityManager): ?int
    {
        $query = $entityManager->createQuery(
            'SELECT n
            FROM App\Entity\Note n
            WHERE n.membre = :currentUser'
        )->setParameter('currentUser', $this->getId());

        $notes = $query->getResult();

        return count($notes);
    }

}
