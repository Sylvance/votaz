<?php

namespace App\Service;

use App\Entity\Candidate;
use App\Entity\Election;
use App\Entity\ElectionRegistration;
use App\Entity\Enum\RegistrationStatus;
use App\Entity\Vote;
use App\Entity\Voter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Lock\LockFactory;

final readonly class VotingService
{
    public function __construct(
        private EntityManagerInterface $em,
        private LockFactory $lockFactory,
    ) {
    }

    public function canVote(Voter $voter, Election $election): bool
    {
        if (!$election->isVotingOpen()) {
            return false;
        }

        $registration = $this->em->getRepository(ElectionRegistration::class)
            ->findOneByElectionAndVoter($election, $voter);

        if (null === $registration || RegistrationStatus::ELIGIBLE !== $registration->getStatus()) {
            return false;
        }

        if ($this->em->getRepository(Vote::class)->findOneBy(['election' => $election, 'voter' => $voter])) {
            return false;
        }

        return true;
    }

    public function castVote(Voter $voter, Election $election, Candidate $candidate): Vote
    {
        $lock = $this->lockFactory->createLock('vote:'.$election->getId().':'.$voter->getId());
        $lock->acquire(true);

        try {
            if (!$this->canVote($voter, $election)) {
                throw new \DomainException('This voter is not allowed to vote in this election.');
            }

            $vote = new Vote();
            $vote->setElection($election);
            $vote->setVoter($voter);
            $vote->setCandidate($candidate);

            $registration = $this->em->getRepository(ElectionRegistration::class)
                ->findOneByElectionAndVoter($election, $voter);
            $registration?->setStatus(RegistrationStatus::VOTED);

            $this->em->persist($vote);
            $this->em->flush();

            return $vote;
        } finally {
            $lock->release();
        }
    }

    public function markVoted(ElectionRegistration $registration): void
    {
        $registration->setStatus(RegistrationStatus::VOTED);
        $this->em->flush();
    }
}
