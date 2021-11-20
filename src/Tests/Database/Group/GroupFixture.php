<?php

namespace App\Tests\Database\Group;

use App\Entity\Group\Group;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Tests\TestData;

class GroupFixture extends Fixture
{
    public const GROUP_REFERENCE = 'group-1';

    public function load(ObjectManager $manager)
    {
        $groupCount = 0;

        $groups = self::generate()->return();
        foreach ($groups as $object) {
            $this->setReference($this::GROUP_REFERENCE, $object);
            $manager->persist($object);
            ++$groupCount;
        }

        $manager->flush();
    }

    public static function generate(): TestData
    {
        return TestData::from(new Group())
            ->with('name', 'testing-web')
            ->with('description', 'that one group to test kiwi')
            ->with('readonly', false)
            ->with('relationable', true)
            ->with('subgroupable', true)
            ->with('active', true)
            ->with('register', false)
        ;
    }
}
