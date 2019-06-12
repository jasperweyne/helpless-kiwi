<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * A person, or multiple persons through a group.
 *
 * @ORM\Entity
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 */
class Reference
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="string", length=100)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=false)
     */
    private $created_at;

    /**
     * Get name.
     *
     * @return string
     */
    public function getName(): ?string
    {
        return $this->id;
    }

    /**
     * Set name.
     *
     * @param string $name
     */
    public function setName(string $name): self
    {
        $this->id = $id;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }
}
