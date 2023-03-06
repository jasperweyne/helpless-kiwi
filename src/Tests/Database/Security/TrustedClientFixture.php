<?php

namespace App\Tests\Database\Security;

use App\Entity\Security\TrustedClient;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;

class TrustedClientFixture extends Fixture
{
    public const ID = 'test';
    public const SECRET = '123secret';

    public function __construct(
        private PasswordHasherFactoryInterface $factory,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $hasher = $this->factory->getPasswordHasher(TrustedClient::class);

        $manager->persist(new TrustedClient(self::ID, $hasher->hash(self::SECRET)));
        $manager->flush();
    }
}
