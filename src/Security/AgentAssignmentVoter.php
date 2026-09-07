<?php

namespace App\Security;

use App\Entity\AgentAssignment;
use App\Entity\PartyAgent;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Grants an agent access to an assignment (and its reports) only when it is theirs.
 */
class AgentAssignmentVoter extends Voter
{
    private const ATTRIBUTES = ['AGENT_ASSIGNMENT'];

    protected function supports(string $attribute, mixed $subject): bool
    {
        return \in_array($attribute, self::ATTRIBUTES, true) && $subject instanceof AgentAssignment;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();
        if (!$user instanceof PartyAgent) {
            return false;
        }

        /** @var AgentAssignment $assignment */
        $assignment = $subject;

        return $assignment->getAgent()?->getId() === $user->getId();
    }
}
