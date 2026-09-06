<?php

namespace App\Service;

use App\Entity\Election;
use App\Entity\Voter;

final readonly class EligibilityService
{
    public function __construct(
        private int $minimumAge,
    ) {
    }

    public function isEligible(Voter $voter, Election $election): bool
    {
        // Must be a confirmed voter.
        if (!$voter->isConfirmed()) {
            return false;
        }

        // Age requirement (default 18).
        if ($voter->getAge() < $this->minimumAge) {
            return false;
        }

        // District constraint: partial/elections scoped to a district only allow
        // voters within that district.
        if (null !== $election->getDistrict()) {
            if ($voter->getDistrict() === null) {
                return false;
            }
            if ($voter->getDistrict()->getId() !== $election->getDistrict()->getId()) {
                return false;
            }
        }

        return true;
    }

    public function reasonsForIneligibility(Voter $voter, Election $election): array
    {
        $reasons = [];

        if (!$voter->isConfirmed()) {
            $reasons[] = 'Your voter registration is not yet confirmed.';
        }

        if ($voter->getAge() < $this->minimumAge) {
            $reasons[] = sprintf('You must be at least %d years old to vote in this election.', $this->minimumAge);
        }

        if (null !== $election->getDistrict()) {
            if ($voter->getDistrict() === null) {
                $reasons[] = 'This election is restricted to specific districts and your voter record has no district assigned.';
            } elseif ($voter->getDistrict()->getId() !== $election->getDistrict()->getId()) {
                $reasons[] = sprintf('This election is restricted to the %s district.', $election->getDistrict()->getName());
            }
        }

        return $reasons;
    }
}