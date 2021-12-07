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
 * @GQL\Description("The root of all query operations.")
 */
class Query
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
     * @GQL\Field(type="[Activity]")
     * @GQL\Description("All currently visible activities.")
     */
    public function activities(bool $loggedIn = false)
    {
        $groups = [];
        if ($loggedIn && $user = $this->user()) {
            $groups = $this->em->getRepository(Group::class)->findAllFor($user);
        }

        return $this->em->getRepository(Activity::class)->findVisibleUpcomingByGroup($groups);
    }

    /**
     * @GQL\Field(type="LocalAccount")
     * @GQL\Description("The currently authenticated user.")
     */
    public function user(): ?LocalAccount
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

    /**
     * @GQL\Field(type="AdminQuery")
     * @GQL\Description("Subquery for administration related data.")
     */
    public function admin(): AdminQuery
    {
        return $this->admin;
    }
}
