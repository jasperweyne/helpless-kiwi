<?php

namespace Tests\Helper\Database\Group;

use App\Entity\Group\Relation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Tests\Helper\Database\Security\LocalAccountFixture;
use Tests\Helper\TestData;

class RelationFixture extends Fixture implements DependentFixtureInterface
{
    public const RELATION_REFERENCE = 'link_admin';

    public function load(ObjectManager $manager)
    {
        $group = $this->getReference(GroupFixture::GROUP_REFERENCE);
        $personId = $this->getReference(LocalAccountFixture::LOCAL_ACCOUNT_REFERENCE);

        $relations = self::generate($group, $personId)->return();
        foreach ($relations as $relation) {
            $manager->persist($relation);
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

    public static function generate($group, $personId): TestData
    {
        return TestData::from(new Relation())
            ->with('group', $group)
            ->with('person_id', $personId->getId())
        ;
    }
}
