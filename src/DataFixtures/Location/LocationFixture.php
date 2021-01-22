<?php

namespace App\DataFixtures\Location;

use App\Entity\Location\Location;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class LocationFixture extends Fixture
{
    public const LOCATION_REFERENCE = 'location';

    public function load(ObjectManager $manager)
    {
        $location = new Location();
        $location->setAddress('');

        $manager->persist($location);
        $this->addReference(self::LOCATION_REFERENCE, $location);

        $manager->flush();
    }
}
