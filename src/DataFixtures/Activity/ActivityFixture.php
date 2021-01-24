<?php

namespace App\DataFixtures\Activity;

use App\DataFixtures\Location\LocationFixture;
use App\Entity\Activity\Activity;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Tests\Helper\TestData;

class ActivityFixture extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $location = $this->getReference(LocationFixture::LOCATION_REFERENCE);

        foreach (self::generate([$location]) as $object) {
            $manager->persist($object);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            LocationFixture::class,
        ];
    }

    public static function generate(array $locations)
    {
        $colors = [
            'red',
            'orange',
            'yellow',
            'green',
            'cyan',
            'ltblue',
            'blue',
            'purple',
            'pink',
        ];

        $i = 0;

        return TestData::from(new Activity())
            ->with('description', '')
            ->with('location', $locations)
            ->with('color', ...$colors)
            ->with('start', new \DateTime('second day of January 2038 18:00'))
            ->with('end', new \DateTime('second day of January 2038 20:00'))
            ->with('deadline', new \DateTime('first day of January 2038'))
            ->with('capacity', 10, null)
            ->do('name', function (Activity $activity) use (&$i) {
                $activity->setName('Activity ' + strval($i++));
            })
            ->return();
    }
}
