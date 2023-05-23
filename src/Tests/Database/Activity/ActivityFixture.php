<?php

namespace App\Tests\Database\Activity;

use App\Entity\Activity\Activity;
use App\Entity\Group\Group;
use App\Entity\Location\Location;
use App\Tests\Database\Group\GroupFixture;
use App\Tests\Database\Location\LocationFixture;
use App\Tests\TestData;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ActivityFixture extends Fixture implements DependentFixtureInterface
{
    public const ACTIVITY_REFERENCE = 'activity-';

    public function load(ObjectManager $manager): void
    {
        /** @var Location */
        $locations = $this->getReference(LocationFixture::LOCATION_REFERENCE);
        /** @var Group */
        $group = $this->getReference(GroupFixture::GROUP_REFERENCE.'0');
        $activityCount = 0;

        $activity = self::generate([$locations], $group)->return();
        foreach ($activity as $object) {
            $this->setReference($this::ACTIVITY_REFERENCE.$activityCount, $object);
            $manager->persist($object);
            ++$activityCount;
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            GroupFixture::class,
            LocationFixture::class,
        ];
    }

    /**
     * @param Location[] $locations
     *
     * @return TestData<Activity>
     */
    public static function generate(array $locations, Group $group): TestData
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
            ->with('author', $group)
            ->with('archived', false)
            ->with('start', new \DateTime('second day January 2038 18:00'))
            ->with('end', new \DateTime('second day January 2038 20:00'))
            ->with('deadline', new \DateTime('first day January 2038'))
            ->with('imageUpdatedAt', new \DateTime('second day January 2038 18:00'))
            ->with('visibleAfter', new \DateTime('1970-01-01'))
            ->do('name', function (Activity $activity) use (&$i) {
                $activity->setName('Activity '.strval($i++));
            })
            ->doWith('location', function (Activity $activity, Location $location) {
                $activity->setLocation($location);
            }, ...$locations)
        ;
    }
}
