<?php

namespace App\Entity\Enum;

enum RoundTableStatus: string
{
    case SCHEDULED = 'scheduled';
    case LIVE = 'live';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::SCHEDULED => 'Scheduled',
            self::LIVE => 'Live now',
            self::COMPLETED => 'Completed',
            self::CANCELLED => 'Cancelled',
        };
    }
}
