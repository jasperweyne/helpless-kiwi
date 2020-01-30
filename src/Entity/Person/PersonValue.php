<?php

namespace App\Entity\Person;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Person\PersonValueRepository")
 */
class PersonValue
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Person\PersonField", fetch="EAGER")
     * @ORM\JoinColumn()
     */
    private $field;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Person\Person", inversedBy="fieldValues", fetch="EAGER")
     * @ORM\JoinColumn(nullable=false)
     */
    private $person;

    /**
     * @ORM\Column(type="text")
     */
    private $value;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $builtin;

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

    public function getField(): ?PersonField
    {
        return $this->field;
    }

    public function setField(?PersonField $field): self
    {
        $this->field = $field;

        return $this;
    }

    public function getPerson(): ?Person
    {
        return $this->person;
    }

    public function setPerson(?Person $person): self
    {
        $this->person = $person;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(string $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getBuiltin(): ?string
    {
        return $this->builtin;
    }

    public function setBuiltin(?string $builtin): self
    {
        $this->builtin = $builtin;

        return $this;
    }

    public function __toString()
    {
        return $this->getValue();
    }
}
