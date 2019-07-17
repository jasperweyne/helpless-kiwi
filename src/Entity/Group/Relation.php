<?php

namespace App\Entity\Group;

use App\Entity\Group\Group;
use App\Entity\Person\Person;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Relation
{
    /**
     * @ORM\Id
     * @ORM\OneToOne(targetEntity="App\Entity\Group\Group")
     */
    private $collection;

    /**
     * @ORM\Id
     * @ORM\OneToOne(targetEntity="App\Entity\Person\Person")
     */
    private $target;

    public function getCollection(): ?Group
    {
        return $this->collection;
    }

    public function setCollection(?Group $collection): self
    {
        $this->collection = $collection;

        return $this;
    }

    public function getTarget(): ?Person
    {
        return $this->target;
    }

    public function setTarget(?Person $target): self
    {
        $this->target = $target;

        return $this;
    }
}
