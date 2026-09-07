<?php

namespace App\Entity\Enum;

enum ElectionType: string
{
    case GENERAL = 'general';
    case BY_ELECTION = 'by_election';
    case PARTIAL = 'partial';
    case REFERENDUM = 'referendum';

    public function label(): string
    {
        return match ($this) {
            self::GENERAL => 'General election',
            self::BY_ELECTION => 'By-election',
            self::PARTIAL => 'Partial election',
            self::REFERENDUM => 'Referendum',
        };
    }
}
