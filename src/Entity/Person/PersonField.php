<?php

namespace App\Entity\Person;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Person\PersonFieldRepository")
 */
class PersonField
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $slug;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $valueType;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $userEditOnly;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Person\PersonScheme", inversedBy="fields")
     */
    private $scheme;

    /**
     * Get id.
     *
     * @return string
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * Set id.
     *
     * @param string $id
     */
    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getValueType(): ?string
    {
        return $this->valueType;
    }

    public function setValueType(string $valueType): self
    {
        $this->valueType = $valueType;

        return $this;
    }

    public function getUserEditOnly(): ?bool
    {
        return $this->userEditOnly;
    }

    public function setUserEditOnly(bool $userEditOnly): self
    {
        $this->userEditOnly = $userEditOnly;

        return $this;
    }

    public function getScheme(): ?PersonScheme
    {
        return $this->scheme;
    }

    public function setScheme(?PersonScheme $scheme): self
    {
        $this->scheme = $scheme;

        return $this;
    }

    public function __toString()
    {
        return $this->getName();
    }
}
