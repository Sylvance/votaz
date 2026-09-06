<?php
namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class SignupRequest
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Email]
        public string $email = '',
        #[Assert\Length(min: 8)]
        public string $password = '',
    ) {
    }
}
