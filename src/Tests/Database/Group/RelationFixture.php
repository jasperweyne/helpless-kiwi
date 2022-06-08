<?php

namespace App\Tests\Database\Group;

use App\Entity\Group\Group;
use App\Entity\Group\Relation;
use App\Entity\Security\LocalAccount;
use App\Tests\Database\Security\LocalAccountFixture;
use App\Tests\TestData;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class RelationFixture extends Fixture implements DependentFixtureInterface
{
    public const RELATION_REFERENCE = 'link_admin';

    public function load(ObjectManager $manager): void
    {
        /** @var Group */
        $group = $this->getReference(GroupFixture::GROUP_REFERENCE);
        /** @var LocalAccount */
        $person = $this->getReference(LocalAccountFixture::LOCAL_ACCOUNT_REFERENCE);

        $relations = self::generate($group, $person)->return();
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

    /**
     * @return TestData<Relation>
     */
    public static function generate(Group $group, LocalAccount $person): TestData
    {
        return TestData::from(new Relation())
            ->with('group', $group)
            ->with('person', $person)
        ;
    }
}
