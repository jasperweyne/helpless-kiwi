<?php

namespace App\Entity\Group;

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
}
