<?php

namespace App\Tests\Database\Group;

use App\Entity\Group\Relation;
use App\Tests\Database\Security\LocalAccountFixture;
use App\Tests\TestData;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class RelationFixture extends Fixture implements DependentFixtureInterface
{
    public const RELATION_REFERENCE = 'local_admin';

    public function load(ObjectManager $manager)
    {
        $group = $this->getReference(GroupFixture::GROUP_REFERENCE.'0');
        $child = $this->getReference(GroupFixture::GROUP_REFERENCE.'1');
        $person = $this->getReference(LocalAccountFixture::LOCAL_ACCOUNT_REFERENCE);

        $relations = self::generate($group, $person)->return();
        $parentrelations = self::generate($child, $group)->return();
        foreach ($relations as $relation) {
            $manager->persist($relation);
        }
        foreach ($parentrelations as $parentrelation) {
            $manager->persist($parentrelation);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            GroupFixture::class,
            LocalAccountFixture::class,
        ];
    }

    public static function generate($group, $person): TestData
    {
        return TestData::from(new Relation())
            ->with('group', $group)
            ->with('person', $person)
        ;
    }

    public static function generatesubgroup($group, $parent): TestData
    {
        return TestData::from(new Relation())
            ->with('group', $group)
            ->with('parent', $parent)
        ;
    }
}
