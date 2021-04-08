<?php

namespace App\Provider\Person;

final class PersonRegistry
{
    private $providers;

    public function __construct($providers)
    {
        $this->providers = $providers;
    }

    public function find(?string $id)
    {
        if (is_null($id)) {
            return null;
        }

        foreach ($this->providers as $provider) {
            $result = $provider->findPerson($id);
            if ($result) {
                return $result;
            }
        }

        return null;
    }

    public function findPersonByEmail(?string $email)
    {
        if (is_null($email)) {
            return null;
        }

        foreach ($this->providers as $provider) {
            $result = $provider->findPersonByEmail($email);
            if ($result) {
                return $result;
            }
        }

        return null;
    }

    public function findAll(): array
    {
        $result = [];
        foreach ($this->providers as $provider) {
            $result = array_merge($result, $provider->findPersons());
        }

        return $result;
    }
}
