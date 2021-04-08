<?php

namespace App\Provider\Person;

interface PersonProviderInterface
{
    public function findPerson(string $id): ?Person;

    public function findPersonByEmail(string $email): ?Person;

    public function findPersons(): array;
}
