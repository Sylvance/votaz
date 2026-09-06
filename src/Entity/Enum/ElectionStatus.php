<?php

namespace App\Entity\Enum;

enum ElectionStatus: string
{
    case DRAFT = 'draft';
    case REGISTRATION_OPEN = 'registration_open';
    case NOMINATION = 'nomination';
    case VOTING = 'voting';
    case RESULTS_PUBLISHED = 'results_published';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::REGISTRATION_OPEN => 'Voter registration open',
            self::NOMINATION => 'Nominations',
            self::VOTING => 'Voting in progress',
            self::RESULTS_PUBLISHED => 'Results published',
            self::CANCELLED => 'Cancelled',
        };
    }
}