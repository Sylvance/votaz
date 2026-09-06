<?php
namespace App\Security;

use App\Entity\Post;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class PostVoter extends Voter
{
    protected function supports(string $attribute, mixed $subject): bool
    {
        return 'POST_EDIT' === $attribute && $subject instanceof Post;
    }
    protected function voteOnAttribute(
        string $attribute,
        mixed $subject,
        TokenInterface $token,
        ?Vote $vote = null,
    ): bool {
        // the entire "who can edit a post?" rule of your app,
        // in a single testable place
        return $subject->getAuthor() === $token->getUser();
    }
}
