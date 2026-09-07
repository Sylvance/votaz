<?php

namespace App\Service;

use App\Entity\Enum\TokenType;
use App\Entity\Voter;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

final readonly class OtpMailer
{
    public function __construct(
        private MailerInterface $mailer,
        private string $institutionName,
        private string $institutionEmail,
    ) {
    }

    public function sendOtp(Voter $voter, string $code, TokenType $type): void
    {
        if (null === $voter->getEmail()) {
            return;
        }

        $subject = 'registration_confirmation' === $type->value
            ? '[%institution%] Confirm your voter registration'
            : '[%institution%] Your login code';

        $email = (new TemplatedEmail())
            ->from($this->institutionEmail)
            ->to($voter->getEmail())
            ->subject(str_replace('%institution%', $this->institutionName, $subject))
            ->htmlTemplate('email/otp.html.twig')
            ->context([
                'institution' => $this->institutionName,
                'voter' => $voter,
                'code' => $code,
                'type' => $type,
            ]);

        $this->mailer->send($email);
    }
}
