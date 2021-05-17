<?php

namespace Tests\Helper\Database\Activity;

use App\Entity\Activity\Activity;
use App\Entity\Location\Location;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Tests\Helper\Database\Location\LocationFixture;
use Tests\Helper\TestData;

class ActivityFixture extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $locations = $this->getReference(LocationFixture::LOCATION_REFERENCE);

        $activity = self::generate([$locations])->return();
        foreach ($activity as $object) {
            $manager->persist($object);
            $this->setReference($object->getName(), $object);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            LocationFixture::class,
        ];
    }

    public static function generate(array $locations): TestData
    {
        $colors = [
            // 'red',
            // 'orange',
            // 'yellow',
            // 'green',
            // 'cyan',
            // 'ltblue',
            // 'blue',
            // 'purple',
            'pink',
        ];

        $i = 0;

        return TestData::from(new Activity())
            ->with('description', 'lol')
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
        ;
    }
}
