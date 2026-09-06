<?php

namespace App\Service;

use App\Entity\Enum\TokenType;
use App\Entity\RegistrationToken;
use App\Entity\Voter;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

final class OtpService
{
    public const EXPIRY_MINUTES = 15;
    public const MAX_ATTEMPTS = 5;

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly OtpMailer $mailer,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * Returns the plaintext code so the caller can relay it (dev preview / logs).
     */
    public function issue(Voter $voter, TokenType $type = TokenType::REGISTRATION_CONFIRMATION): array
    {
        foreach ($this->em->getRepository(RegistrationToken::class)->findActive($voter, $type) as $token) {
            $token->setUsedAt(new \DateTimeImmutable());
        }

        $code = (string) random_int(100000, 999999);
        $token = new RegistrationToken();
        $token->setVoter($voter);
        $token->setType($type);
        $token->setCodeHash(hash('sha256', $code));
        $token->setExpiresAt(new \DateTimeImmutable(sprintf('+%d minutes', self::EXPIRY_MINUTES)));

        $this->em->persist($token);
        $this->em->flush();

        $this->mailer->sendOtp($voter, $code, $type);

        return [$token, $code];
    }

    public function verify(RegistrationToken $token, string $code): bool
    {
        if ($token->isExpired()) {
            return false;
        }

        if ($token->getAttempts() >= self::MAX_ATTEMPTS) {
            return false;
        }

        $token->setAttempts($token->getAttempts() + 1);

        if (!hash_equals($token->getCodeHash() ?? '', hash('sha256', $code))) {
            $this->em->flush();

            return false;
        }

        $token->setUsedAt(new \DateTimeImmutable());
        $this->em->flush();

        return true;
    }
}