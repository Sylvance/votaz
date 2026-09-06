<?php
namespace App\MessageHandler;

use App\Message\SendWelcomeEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Mime\Email;

#[AsMessageHandler]
class SendWelcomeEmailHandler
{
    public function __construct(
        private MailerInterface $mailer,
    ) {
    }
    public function __invoke(SendWelcomeEmail $message): void
    {
        $email = (new Email())
            ->to($message->userEmail)
            ->subject('Welcome aboard!')
            ->text('Thanks for signing up.');
        $this->mailer->send($email);
    }
}