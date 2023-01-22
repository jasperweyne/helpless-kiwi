<?php

namespace App\Tests\Database\Security;

use App\Entity\Security\TrustedClient;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TrustedClientFixture extends Fixture
{
    public const ID = 'test';
    public const SECRET = '123secret';

    public function load(ObjectManager $manager): void
    {
        $manager->persist(new TrustedClient(self::ID, self::SECRET));
        $manager->flush();
    }
}
