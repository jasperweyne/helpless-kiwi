<?php

namespace App\GraphQL;

use App\Entity\Activity\Activity;
use Doctrine\ORM\EntityManagerInterface;
use Overblog\GraphQLBundle\Annotation as GQL;

/**
 * @GQL\Type
 */
class RootQuery
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @GQL\Field(name="ping", type="String!")
     */
    public function ping()
    {
        return 'Hello world!';
    }

    /**
     * @GQL\Field(name="activityData", type="[Activity]")
     */
    public function data()
    {
        return $this->em->getRepository(Activity::class)->findUpcomingByGroup([]);
    }
}
