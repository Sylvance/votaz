<?php

namespace App\Service;

final class CodeGenerator
{
    public static function voterNumber(): string
    {
        return sprintf('VN-%04d-%06d', random_int(0, 9999), random_int(0, 999999));
    }

    public static function receipt(string $prefix = 'REG', ?string $year = null): string
    {
        return sprintf('%s-%s-%06d', $prefix, $year ?? (new \DateTimeImmutable())->format('Y'), random_int(0, 999999));
    }

    public static function confirmationCode(): string
    {
        $year = (new \DateTimeImmutable())->format('Y');

        return sprintf('VOT-%s-%09d', $year, random_int(0, 999999999));
    }

    public static function otp(): string
    {
        return (string) random_int(100000, 999999);
    }

    public static function electionCode(): string
    {
        return strtoupper(substr(bin2hex(random_bytes(6)), 0, 8));
    }

    public static function partyRegistrationNumber(): string
    {
        return sprintf('PRT-%s-%05d', (new \DateTimeImmutable())->format('Y'), random_int(0, 99999));
    }

    public static function agentCode(): string
    {
        $year = (new \DateTimeImmutable())->format('Y');

        return sprintf('AGT-%s-%06d', $year, random_int(0, 999999));
    }
}