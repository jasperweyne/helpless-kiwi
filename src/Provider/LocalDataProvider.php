<?php

namespace App\Provider;

use App\Entity\Security\LocalAccount;
use App\Provider\Person\Person;
use App\Provider\Person\PersonProviderInterface;
use Doctrine\ORM\EntityManagerInterface;

class LocalDataProvider implements PersonProviderInterface
{
    protected $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function findPerson(string $id): ?Person
    {
        $auth = $this->em->getRepository(LocalAccount::class)->find($id);

        return $this->convert($auth);
    }

    public function findPersons(): array
    {
        return array_map([$this, 'convert'], $this->em->getRepository(LocalAccount::class)->findAll());
    }

    private function convert(?LocalAccount $auth): ?Person
    {
        if (is_null($auth))
            return null;

        $person = new Person();
        $person
            ->setId($auth->getId())
            ->setEmail($auth->getEmail())
            ->setFields(['name' => $auth->getEmail()])
        ;

        return $person;
    }
}