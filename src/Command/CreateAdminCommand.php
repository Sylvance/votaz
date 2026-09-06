<?php

namespace App\Command;

use App\Entity\Enum\VoterStatus;
use App\Entity\Voter;
use App\Repository\VoterRepository;
use App\Service\CodeGenerator;
use App\Service\VoterRegistrationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:create-admin',
    description: 'Create an administrator account for the electoral commission',
)]
class CreateAdminCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly VoterRepository $voterRepository,
        private readonly VoterRegistrationService $registrationService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::OPTIONAL, 'Admin email address')
            ->addArgument('password', InputArgument::OPTIONAL, 'Admin password');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $email = $input->getArgument('email');
        if (null === $email) {
            $email = $io->ask('Admin email');
        }
        $email = trim((string) $email);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $io->error('Invalid email address.');

            return Command::FAILURE;
        }

        $password = $input->getArgument('password');
        if (null === $password) {
            $password = $io->askHidden('Password');
        }
        if (null === $password || strlen((string) $password) < 8) {
            $io->error('Password must be at least 8 characters.');

            return Command::FAILURE;
        }

        $existing = $this->voterRepository->findOneByUsernameOrEmail($email);
        if (null !== $existing) {
            $io->error(sprintf('A voter with email %s already exists.', $email));

            return Command::FAILURE;
        }

        $voter = new Voter();
        $voter->setUsername($email);
        $voter->setEmail($email);
        $voter->setNationalId('ADMIN-'.strtoupper(substr(md5((string) random_int(0, 999999)), 0, 8)));
        $voter->setVoterNumber(CodeGenerator::voterNumber());
        $voter->setFirstName('Electoral');
        $voter->setLastName('Commission');
        $voter->setGender('O');
        $voter->setDateOfBirth(new \DateTimeImmutable('1980-01-01'));
        $voter->setPassword(password_hash((string) $password, PASSWORD_DEFAULT));
        $voter->setRoles(['ROLE_ADMIN', 'ROLE_VOTER']);
        $voter->setStatus(VoterStatus::CONFIRMED);
        $voter->setConfirmedAt(new \DateTimeImmutable());
        $voter->setConfirmationCode(CodeGenerator::confirmationCode());

        $this->em->persist($voter);
        $this->em->flush();

        $io->success([
            'Administrator created.',
            sprintf('Login: %s', $email),
        ]);

        return Command::SUCCESS;
    }
}