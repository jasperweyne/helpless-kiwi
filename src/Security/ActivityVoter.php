<?php

namespace App\Security;

use App\Entity\Activity\Activity;
use App\Entity\Security\LocalAccount;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ActivityVoter extends Voter
{
    public const VIEW = 'view_activity';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return self::VIEW === $attribute && $subject instanceof Activity;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        /** @var Activity $activity */
        $activity = $subject;

        $user = $token->getUser();
        if ($user instanceof LocalAccount && in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return true;
        }

        return $activity->isVisibleBy($user instanceof LocalAccount ? $user : null);
    }
}
