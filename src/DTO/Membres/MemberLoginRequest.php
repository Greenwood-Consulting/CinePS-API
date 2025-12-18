<?php
namespace App\DTO\Membres;


use Symfony\Component\Validator\Constraints as Assert;

class MemberLoginRequest
{
    #[Assert\NotBlank]
    #[Assert\Email]
    public string $email;


    #[Assert\NotBlank]
    #[Assert\Length(min: 4)]
    public string $password;
}
