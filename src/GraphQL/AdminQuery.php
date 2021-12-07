<?php

namespace App\GraphQL;

use App\Entity\Activity\Activity;
use App\Entity\Group\Group;
use App\Entity\Security\LocalAccount;
use Doctrine\ORM\EntityManagerInterface;
use Overblog\GraphQLBundle\Annotation as GQL;

/**
 * @GQL\Type
 * @GQL\Access("hasRole('ROLE_ADMIN')")
 * @GQL\Description("The root query for all administration related data.")
 */
class AdminQuery
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @GQL\Field(type="[Activity]")
     * @GQL\Description("All activities stored in the database.")
     */
    public function activities()
    {
        return $this->em->getRepository(Activity::class)->findAll();
    }

    /**
     * @GQL\Field(type="[Group]")
     * @GQL\Description("All groups stored in the database.")
     */
    public function groups()
    {
        return $this->em->getRepository(Group::class)->findAll();
    }

    /**
     * @GQL\Field(type="[LocalAccount]")
     * @GQL\Description("All users stored in the database.")
     */
    public function users()
    {
        return $this->em->getRepository(LocalAccount::class)->findAll();
    }
}
