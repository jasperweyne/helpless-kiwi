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
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    public function __construct(EntityManagerInterface $em, TokenStorageInterface $tokenStorage)
    {
        $this->em = $em;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @GQL\Field(type="[Activity]")
     * @GQL\Description("All currently visible activities.")
     *
     * @return Activity[]
     */
    public function current(bool $loggedIn = false): array
    {
        $groups = [];
        if ($loggedIn && $user = $this->user()) {
            $groups = $this->em->getRepository(Group::class)->findAllFor($user);
        }

        return $this->em->getRepository(Activity::class)->findVisibleUpcomingByGroup($groups);
    }

    /**
     * @GQL\Field(type="[Activity]")
     * @GQL\Description("All activities stored in the database.")
     * @GQL\Access("isAuthenticated()")
     *
     * @return Activity[]
     */
    public function activities(): array
    {
        return $this->em->getRepository(Activity::class)->findAll();
    }

    /**
     * @GQL\Field(type="[Group]")
     * @GQL\Description("All groups stored in the database.")
     * @GQL\Access("isAuthenticated()")
     *
     * @return Group[]
     */
    public function groups(): array
    {
        return $this->em->getRepository(Group::class)->findAll();
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
     * @GQL\Field(type="[LocalAccount]")
     * @GQL\Description("All users stored in the database.")
     * @GQL\Access("hasRole('ROLE_ADMIN')")
     *
     * @return LocalAccount[]
     */
    public function users()
    {
        return $this->em->getRepository(LocalAccount::class)->findAll();
    }
}
