<?php

namespace App\Service;

use App\Entity\Election;
use App\Entity\Enum\CandidateStatus;
use App\Entity\Vote;
use Doctrine\ORM\EntityManagerInterface;

final readonly class ResultsService
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    /**
     * @return array{total_votes: int, by_candidate: array<int, array{candidate: object, votes: int}>, turnout_pct: float|null}
     */
    public function tally(Election $election): array
    {
        $candidates = $election->getApprovedCandidates();
        $votes = $this->em->getRepository(Vote::class)->tally($election);

        $byCandidate = [];
        $votedCount = 0;
        foreach ($candidates as $candidate) {
            $count = 0;
            foreach ($votes as $v) {
                if ((int) $v['candidate_id'] === $candidate->getId()) {
                    $count = (int) $v['total'];
                    break;
                }
            }
            $votedCount += $count;
            $byCandidate[$candidate->getId()] = ['candidate' => $candidate, 'votes' => $count];
        }

        uasort($byCandidate, static fn (array $a, array $b): int => $b['votes'] <=> $a['votes']);

        $eligible = $this->em->getRepository(\App\Entity\ElectionRegistration::class)
            ->countForElection($election, \App\Entity\Enum\RegistrationStatus::ELIGIBLE)
            + $this->em->getRepository(\App\Entity\ElectionRegistration::class)
                ->countForElection($election, \App\Entity\Enum\RegistrationStatus::VOTED);

        $turnout = $eligible > 0 ? round($votedCount / $eligible * 100, 1) : null;

        return [
            'total_votes' => $votedCount,
            'by_candidate' => $byCandidate,
            'turnout_pct' => $turnout,
            'eligible' => $eligible,
        ];
    }
}