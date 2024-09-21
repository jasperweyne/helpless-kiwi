<?php

namespace App\Tests;

use Faker\Provider\Base as BaseProvider;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;

class TestFunctionProvider extends BaseProvider
{
    public function __construct(
        private PasswordHasherInterface $hasher
    ) {
    }

    public function hash(string $value)
    {
        return $this->hasher->hash($value);
    }
}
