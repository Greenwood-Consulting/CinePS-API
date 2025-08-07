<?php
namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

class PreSelectionInput
{
    #[Assert\NotNull]
    #[Assert\Type('integer')]
    #[Groups(['preselection:write'])]
    public int $membre_id;

    #[Assert\NotBlank]
    #[Assert\Type('string')]
    #[Groups(['preselection:write'])]
    public string $theme;
}