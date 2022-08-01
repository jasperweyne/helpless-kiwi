<?php

namespace App\Tests\Database\Group;

use App\Entity\Group\Group;
use App\Tests\TestData;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class GroupFixture extends Fixture
{
    public const GROUP_REFERENCE = 'group-';

    public function load(ObjectManager $manager): void
    {
        $groupCount = 0;

        $groups = self::generate()->return();
        foreach ($groups as $object) {
            $this->setReference($this::GROUP_REFERENCE.$groupCount, $object);
            $manager->persist($object);
            ++$groupCount;
        }

        $manager->flush();
    }

    /**
     * @return TestData<Group>
     */
    public static function generate(): TestData
    {
        $parent = null;

        return TestData::from(new Group())
            ->with('name', 'test 1', 'test 2')
            ->with('description', 'testgroup')
            ->with('readonly', false)
            ->with('relationable', true)
            ->with('subgroupable', true)
            ->with('active', true)
            ->with('register', false)
            ->do('parent', function (Group $group) use (&$parent) {
                $group->setParent($parent);
                $parent = $group;
            })
        ;
    }
}
