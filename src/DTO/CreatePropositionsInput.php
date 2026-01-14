<?php
namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

class CreatePropositionsInput
{
    #[Assert\NotNull]
    #[Assert\Positive]
    #[Groups(['propositions:write'])]
    public int $preselection_id;
}
