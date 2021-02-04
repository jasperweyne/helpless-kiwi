<?php

namespace App\DataFixtures\Activity;

use App\DataFixtures\Location\LocationFixture;
use App\Entity\Activity\Activity;
use App\Entity\Location\Location;
use App\Tests\Helper\TestData;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ActivityFixture extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $locations = $this->getReference(LocationFixture::LOCATION_REFERENCE);

        foreach (self::generate([$locations]) as $object) {
            $manager->persist($object);
            $this->setReference($object->getName(), $object);
        }

        $manager->flush();
//        \DAMA\DoctrineTestBundle\Doctrine\DBAL\StaticDriver::commit();
//        die;
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
            //            'red',
            //            'orange',
            //            'yellow',
            //            'green',
            //            'cyan',
            //            'ltblue',
            //            'blue',
            //            'purple',
            'pink',
        ];

        $i = 0;

        return TestData::from(new Activity())
            ->with('description', '')
            ->with('color', ...$colors)
            ->with('start', new \DateTime('second day January 2038 18:00'))
            ->with('end', new \DateTime('second day January 2038 20:00'))
            ->with('deadline', new \DateTime('first day January 2038'))
            ->with('imageUpdatedAt', new \DateTime('second day January 2038 18:00'))
            ->do('name', function (Activity $activity) use (&$i) {
                $activity->setName('Activity '.strval($i++));
            })
            ->doWith('location', function (Activity $activity, Location $location) {
                $activity->setLocation($location);
            }, ...$locations)
            ->return();
    }
}
