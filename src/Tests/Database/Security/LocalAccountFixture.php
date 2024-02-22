<?php

namespace App\Tests\Database\Security;

use App\Entity\Security\LocalAccount;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class LocalAccountFixture extends Fixture
{
    public const LOCAL_ACCOUNT_REFERENCE = 'local_admin';
    public const USERNAME = 'admin@test.nl';

    public function __construct(
        private UserPasswordHasherInterface $encoder
    ) {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager): void
    {
        $localAccount = new LocalAccount();
        $localAccount->setName('admin');
        $localAccount->setEmail(self::USERNAME);
        $localAccount->setPassword($this->encoder->hashPassword($localAccount, 'root'));
        $localAccount->setRoles(['ROLE_ADMIN']);

        $manager->persist($localAccount);
        $this->addReference(self::LOCAL_ACCOUNT_REFERENCE, $localAccount);

        $manager->flush();
    }
}
