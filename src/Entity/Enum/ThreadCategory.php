<?php

namespace App\Entity\Enum;

enum ThreadCategory: string
{
    case GENERAL = 'general';
    case ELECTION = 'election';
    case PARTY = 'party';
    case ROUND_TABLE = 'round_table';

    public function label(): string
    {
        return match ($this) {
            self::GENERAL => 'General discussion',
            self::ELECTION => 'Election',
            self::PARTY => 'Party',
            self::ROUND_TABLE => 'Round table',
        };
    }
}