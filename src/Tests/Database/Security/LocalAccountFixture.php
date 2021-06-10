<?php

namespace App\Tests\Database\Security;

use App\Entity\Security\LocalAccount;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class LocalAccountFixture extends Fixture
{
    public const LOCAL_ACCOUNT_REFERENCE = 'admin';
    public const USERNAME = 'admin@test.nl';

    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        $localAccount = new LocalAccount();
        $localAccount->setName('admin');
        $localAccount->setEmail(self::USERNAME);
        $localAccount->setPassword($this->encoder->encodePassword($localAccount, 'root'));
        $localAccount->setRoles(['ROLE_ADMIN']);

        $manager->persist($localAccount);
        $this->addReference(self::LOCAL_ACCOUNT_REFERENCE, $localAccount);

        $manager->flush();
    }
}
