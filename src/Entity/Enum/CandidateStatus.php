<?php

namespace App\Entity\Enum;

enum CandidateStatus: string
{
    case NOMINATED = 'nominated';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case WITHDRAWN = 'withdrawn';

    public function label(): string
    {
        return match ($this) {
            self::NOMINATED => 'Nominated',
            self::APPROVED => 'Approved',
            self::REJECTED => 'Rejected',
            self::WITHDRAWN => 'Withdrawn',
        };
    }
}
