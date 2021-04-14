<?php

namespace App\Provider\Person;

interface PersonRegistryInterface
{
    public function find(?string $id);

    public function findAll(): array;
}
