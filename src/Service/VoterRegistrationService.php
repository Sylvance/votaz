<?php

namespace App\Service;

use App\Entity\Election;
use App\Entity\ElectionRegistration;
use App\Entity\Enum\RegistrationStatus;
use App\Entity\Enum\TokenType;
use App\Entity\Enum\VoterStatus;
use App\Entity\RegistrationToken;
use App\Entity\Voter;
use Doctrine\ORM\EntityManagerInterface;

final readonly class VoterRegistrationService
{
    public function __construct(
        private EntityManagerInterface $em,
        private OtpService $otpService,
    ) {
    }

    public function createVoter(Voter $voter): Voter
    {
        $voter->setVoterNumber($this->generateVoterNumber());
        $voter->setUsername($voter->getVoterNumber());
        $voter->setPassword(password_hash(bin2hex(random_bytes(12)), PASSWORD_DEFAULT));
        if (null !== $voter->getEmail()) {
            $voter->setUsername($voter->getEmail());
        }

        $voter->setTermsAcceptedAt(new \DateTimeImmutable());
        $voter->setRoles(['ROLE_VOTER']);

        $this->em->persist($voter);
        $this->em->flush();

        return $voter;
    }

    public function sendConfirmationOtp(Voter $voter): array
    {
        return $this->otpService->issue($voter);
    }

    public function confirmVoter(Voter $voter, string $code, string $password): bool
    {
        if (null === $voter->getEmail() && null === $voter->getVoterNumber()) {
            return false;
        }

        $tokens = $this->em->getRepository(RegistrationToken::class)
            ->findActive($voter, TokenType::REGISTRATION_CONFIRMATION);

        $verified = false;
        foreach ($tokens as $token) {
            if ($this->otpService->verify($token, $code)) {
                $verified = true;
                break;
            }
        }

        if (!$verified) {
            return false;
        }

        $voter->setPassword($this->hashPassword($password));
        $voter->setConfirmationCode($this->issueConfirmationCode());
        $voter->setStatus(VoterStatus::CONFIRMED);
        $voter->setConfirmedAt(new \DateTimeImmutable());
        $voter->setUsername($voter->getEmail() ?? $voter->getUsername());

        $this->em->flush();

        return true;
    }

    public function createElectionRegistration(Voter $voter, Election $election): ElectionRegistration
    {
        $existing = $this->em->getRepository(ElectionRegistration::class)
            ->findOneByElectionAndVoter($election, $voter);

        if (null !== $existing) {
            return $existing;
        }

        $registration = new ElectionRegistration();
        $registration->setElection($election);
        $registration->setVoter($voter);
        $registration->setStatus(RegistrationStatus::PENDING);
        $registration->setRegisteredAt(new \DateTimeImmutable());

        $this->em->persist($registration);
        $this->em->flush();

        return $registration;
    }

    public function approveElectionRegistration(ElectionRegistration $registration): void
    {
        $registration->setStatus(RegistrationStatus::ELIGIBLE);
        $registration->setVerifiedAt(new \DateTimeImmutable());
        $registration->setRejectionReason(null);

        if (null === $registration->getReceiptNumber()) {
            $registration->setReceiptNumber($this->generateReceiptNumber());
        }

        $this->em->flush();
    }

    public function rejectElectionRegistration(ElectionRegistration $registration, string $reason): void
    {
        $registration->setStatus(RegistrationStatus::REJECTED);
        $registration->setRejectionReason($reason);
        $this->em->flush();
    }

    public function withdrawElectionRegistration(ElectionRegistration $registration): void
    {
        $this->em->remove($registration);
        $this->em->flush();
    }

    public function generateReceiptNumber(): string
    {
        $prefix = ElectionRegistration::RECEIPT_PREFIX;
        $year = (new \DateTimeImmutable())->format('Y');
        do {
            $receipt = sprintf('%s-%s-%06d', $prefix, $year, random_int(0, 999999));
        } while (null !== $this->em->getRepository(ElectionRegistration::class)->findOneBy(['receiptNumber' => $receipt]));

        return $receipt;
    }

    public function issueConfirmationCode(): string
    {
        $year = (new \DateTimeImmutable())->format('Y');
        do {
            $code = sprintf(
                'VOT-%s-%09d',
                $year,
                random_int(0, 999999999),
            );

            $exists = $this->em->getRepository(Voter::class)->findOneBy(['confirmationCode' => $code]);
        } while (null !== $exists);

        return $code;
    }

    private function generateVoterNumber(): string
    {
        do {
            $number = sprintf('VN-%04d-%06d', random_int(0, 9999), random_int(0, 999999));
        } while (null !== $this->em->getRepository(Voter::class)->findOneBy(['voterNumber' => $number]));

        return $number;
    }

    private function hashPassword(string $plain): string
    {
        return password_hash($plain, PASSWORD_DEFAULT);
    }
}