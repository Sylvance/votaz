<?php
namespace App\Command;

use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:send-newsletter',
    description: 'Sends the weekly newsletter',
)]
class SendNewsletterCommand
{
    public function __invoke(
        SymfonyStyle $io,
        #[Argument] string $segment = 'all',
    ): int {
        $io->title("Sending the newsletter to the '$segment' segment");
        // ... your business logic
        $io->success('Newsletter sent!');
        return 0;
    }
}
