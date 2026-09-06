<?php

namespace App\Entity\Enum;

enum RegistrationStatus: string
{
    case PENDING = 'pending';
    case ELIGIBLE = 'eligible';
    case REJECTED = 'rejected';
    case VOTED = 'voted';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::ELIGIBLE => 'Eligible',
            self::REJECTED => 'Rejected',
            self::VOTED => 'Voted',
        };
    }
}