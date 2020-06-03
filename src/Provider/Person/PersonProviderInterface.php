<?php

namespace App\Provider\Person;

interface PersonProviderInterface
{
    public function findPerson(string $id): ?Person;
    public function findPersons(): array;
}