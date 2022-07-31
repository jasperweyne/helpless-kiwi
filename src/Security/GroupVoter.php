<?php

namespace App\Security;

use App\Entity\Group\Group;
use App\Entity\Group\Relation;
use App\Entity\Security\LocalAccount;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class GroupVoter extends Voter
{
    public const IN_GROUP = 'in_group';
    public const EDIT_GROUP = 'edit_group';
    public const ANY_GROUP = 'any_group';

    protected function supports($attribute, $subject): bool
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [self::IN_GROUP, self::EDIT_GROUP, self::ANY_GROUP], true)) {
            return false;
        }

        // only vote on `Group` objects or null
        if (null !== $subject && !$subject instanceof Group) {
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
        if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return true;
        }

        // you know $subject is a Group object, thanks to `supports()`
        /** @var ?Group $group */
        $group = $subject;

        switch ($attribute) {
            case self::IN_GROUP:
                return $this->validGroup($group, $user);
            case self::EDIT_GROUP:
                return $this->editGroup($group, $user);
            case self::ANY_GROUP:
                return $this->anyGroup($user);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function validGroup(?Group $group, LocalAccount $user): bool
    {
        // if group is null, assume user may not pass (unless admin, see above)
        if (null === $group) {
            return false;
        }

        // find all relations in the hierarchy, and check if one of the (parent)
        // groups is an active group
        return $group->getAllRelationFor($user)->exists(function ($i, Relation $relation) {
            $current = $relation->getGroup();

            return null !== $current && true === $current->isActive();
        });
    }

    private function editGroup(?Group $group, LocalAccount $user): bool
    {
        // if group is null, assume user may not pass (unless admin, see above)
        if (null === $group) {
            return false;
        }

        // find all relations in the hierarchy, and check if one of the parent
        // groups is an active group (not this group itself, only parent group
        // members can edit group settings)
        return $group->getAllRelationFor($user)->exists(function ($i, Relation $relation) use ($group) {
            $current = $relation->getGroup();

            return null !== $current && true === $current->isActive() && $current !== $group;
        });
    }

    private function anyGroup(LocalAccount $user): bool
    {
        // if in one of the (active) groups
        foreach ($user->getRelations() as $relation) {
            if (null !== $relation->getGroup() && true === $relation->getGroup()->isActive()) {
                return true;
            }
        }

        return false;
    }
}
