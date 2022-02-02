<?php

namespace App\Security;

use App\Entity\Group\Group;
use App\Entity\Security\LocalAccount;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class GroupVoter extends Voter
{
    const IN_GROUP = 'in_group';
    const ANY_GROUP = 'any_group';

    protected function supports($attribute, $subject): bool
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [self::IN_GROUP, self::ANY_GROUP])) {
            return false;
        }

        // only vote on `Group` objects or null
        if ($subject && !$subject instanceof Group) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof LocalAccount) {
            // the user must be logged in; if not, deny access
            return false;
        }

        // admins always have access
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return true;
        }

        // you know $subject is a Group object, thanks to `supports()`
        /** @var ?Group $group */
        $group = $subject;

        switch ($attribute) {
            case self::IN_GROUP:
                return $this->validGroup($group, $user);
            case self::ANY_GROUP:
                return $this->anyGroup($user);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function validGroup(?Group $group, LocalAccount $user): bool
    {
        // if group is null, assume user may not pass (unless admin, see above)
        if (!$group) {
            return false;
        }

        // if one of the (active) groups
        foreach ($user->getRelations() as $relation) {
            if ($group->isActive() && $group === $relation->getGroup()) {
                return true;
            }
        }

        return false;
    }

    private function anyGroup(LocalAccount $user): bool
    {
        // if one of the (active) groups
        foreach ($user->getRelations() as $relation) {
            if ($relation->getGroup()->isActive()) {
                return true;
            }
        }

        return false;
    }
}
