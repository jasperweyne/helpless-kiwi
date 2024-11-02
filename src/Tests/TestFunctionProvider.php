<?php

namespace App\Tests;

use App\Entity\Security\LocalAccount;
use Faker\Provider\Base as BaseProvider;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class TestFunctionProvider extends BaseProvider
{
    public function __construct(
        private UserPasswordHasherInterface $hasher,
    ) {
    }

    public function hashPassword(string $value)
    {
        return $this->hasher->hashPassword(new LocalAccount(), $value);
    }
}
