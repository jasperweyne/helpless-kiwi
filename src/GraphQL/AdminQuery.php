<?php

namespace App\GraphQL;

use App\Entity\Activity\Activity;
use Doctrine\ORM\EntityManagerInterface;
use Overblog\GraphQLBundle\Annotation as GQL;

/**
 * @GQL\Type
 * @GQL\Access("hasRole('ROLE_ADMIN')")
 */
class AdminQuery
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @GQL\Field(name="activities", type="[Activity]")")
     */
    public function findActivities()
    {
        return $this->em->getRepository(Activity::class)->findAll();
    }
}
