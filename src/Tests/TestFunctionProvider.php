<?php

namespace App\Tests;

use App\Entity\Security\LocalAccount;
use Faker\Generator;
use Faker\Provider\Base as BaseProvider;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class TestFunctionProvider extends BaseProvider
{
    public function __construct(
        Generator $generator,
        private UserPasswordHasherInterface $hasher,
    ) {
        parent::__construct($generator);
    }

    public function hashPassword(string $value): string
    {
        return $this->hasher->hashPassword(new LocalAccount(), $value);
    }
}
