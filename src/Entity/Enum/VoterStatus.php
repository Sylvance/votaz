<?php

namespace App\Entity\Enum;

enum VoterStatus: string
{
    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    case REJECTED = 'rejected';
    case DEACTIVATED = 'deactivated';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending verification',
            self::CONFIRMED => 'Confirmed',
            self::REJECTED => 'Rejected',
            self::DEACTIVATED => 'Deactivated',
        };
    }
}
