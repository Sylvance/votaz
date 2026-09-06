<?php

namespace App\Entity\Enum;

enum QuestionType: string
{
    case SINGLE_CHOICE = 'single';
    case MULTIPLE_CHOICE = 'multiple';

    public function label(): string
    {
        return match ($this) {
            self::SINGLE_CHOICE => 'Single choice',
            self::MULTIPLE_CHOICE => 'Multiple choice',
        };
    }
}