<?php
namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

class CreateFilmInput
{
    #[Assert\NotBlank]
    #[Groups(['film:write'])]
    public string $titre;

    #[Assert\NotNull]
    #[Groups(['film:write'])]
    public \DateTimeInterface $date;

    #[Assert\NotNull]
    #[Assert\Type('integer')]
    #[Groups(['film:write'])]
    public int $sortie_film;

    #[Assert\NotBlank]
    #[Groups(['film:write'])]
    public string $imdb;

    // To make a relation with a preselection
    #[Assert\Type('integer')]
    #[Groups(['film:write'])]
    public ?int $pre_selection_id;
}
