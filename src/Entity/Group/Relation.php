<?php

namespace App\Entity\Group;

use App\Entity\Reference;
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
     * @ORM\OneToOne(targetEntity="App\Entity\Reference")
     */
    private $target;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * Get group name.
     *
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Set group name.
     *
     * @param string $name
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getCollection(): ?Group
    {
        return $this->collection;
    }

    public function setCollection(?Group $collection): self
    {
        $this->collection = $collection;

        return $this;
    }

    public function getTarget(): ?Reference
    {
        return $this->target;
    }

    public function setTarget(?Reference $target): self
    {
        $this->target = $target;

        return $this;
    }
}
