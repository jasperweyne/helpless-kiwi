<?php

namespace App\GraphQL;

use App\Entity\Activity\Activity;
use App\Entity\Group\Group;
use App\Entity\Security\LocalAccount;
use Doctrine\ORM\EntityManagerInterface;
use Overblog\GraphQLBundle\Annotation as GQL;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @GQL\Type
 */
class RootQuery
{
    private $em;

    private $tokenStorage;

    private $admin;

    public function __construct(EntityManagerInterface $em, TokenStorageInterface $tokenStorage, AdminQuery $admin)
    {
        $this->em = $em;
        $this->tokenStorage = $tokenStorage;
        $this->admin = $admin;
    }

    /**
     * @GQL\Field(name="activities", type="[Activity]")
     */
    public function findActivities(bool $loggedIn = false)
    {
        $groups = [];
        if ($loggedIn && $user = $this->getUser()) {
            $groups = $this->em->getRepository(Group::class)->findAllFor($user);
        }

        return $this->em->getRepository(Activity::class)->findUpcomingByGroup($groups);
    }

    /**
     * @GQL\Field(name="admin", type="AdminQuery")
     */
    public function getAdmin()
    {
        return $this->admin;
    }

    private function getUser(): ?LocalAccount
    {
        if (null === $token = $this->tokenStorage->getToken()) {
            return null;
        }

        if (!\is_object($user = $token->getUser())) {
            // e.g. anonymous authentication
            return null;
        }

        return $user;
    }
}
