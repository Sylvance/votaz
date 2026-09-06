<?php

namespace App\Entity\Enum;

enum TokenType: string
{
    case REGISTRATION_CONFIRMATION = 'registration_confirmation';
    case LOGIN = 'login';
}