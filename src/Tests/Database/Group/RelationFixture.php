<?php

namespace App\Tests\Database\Group;

use App\Entity\Group\Group;
use App\Entity\Security\LocalAccount;
use App\Tests\Database\Security\LocalAccountFixture;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class RelationFixture extends Fixture implements DependentFixtureInterface
{
    public const RELATION_REFERENCE = 'local_admin';

    public function load(ObjectManager $manager): void
    {
        /** @var Group */
        $group = $this->getReference(GroupFixture::GROUP_REFERENCE.'0');
        /** @var LocalAccount */
        $person = $this->getReference(LocalAccountFixture::LOCAL_ACCOUNT_REFERENCE);

        $group->addRelation($person);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            GroupFixture::class,
            LocalAccountFixture::class,
        ];
    }
}
