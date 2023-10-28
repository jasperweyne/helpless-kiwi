<?php

namespace App\Tests\Database\Location;

use App\Entity\Location\Location;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class LocationFixture extends Fixture
{
    public const LOCATION_REFERENCE = 'localhost';

    public function load(ObjectManager $manager): void
    {
        $location = new Location();
        $location->setName('here');
        $location->setAddress('@localhost');

        $location2 = new Location();
        $location2->setName('there');
        $location2->setAddress('@externalhost');

        $manager->persist($location);
        $manager->persist($location2);
        $this->addReference(self::LOCATION_REFERENCE, $location);

        $manager->flush();
    }
}
