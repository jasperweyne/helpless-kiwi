<?php

namespace App\Provider\Person;

class Person
{
    private $id;

    private $email;

    private $fields;

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

    /**
     * Get email address.
     *
     * @return string
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * Set email address.
     *
     * @param string $email
     */
    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get fields.
     *
     * @return array
     */
    public function getFields(): array
    {
        return $this->fields ?? [];
    }

    /**
     * Set fields.
     *
     * @param array $fields
     */
    public function setFields(array $fields): self
    {
        $this->fields = $fields;

        return $this;
    }

    public function getName(): ?string
    {
        if (array_key_exists('name', $this->getFields()))
            return $this->fields['name'];
        if (array_key_exists('given_name', $this->getFields()) || array_key_exists('family_name', $this->getFields()))
            return \trim($this->fields['given_name'] . $this->fields['family_name']);

        return null;
    }

    public function getShortname(): ?string
    {
        if (array_key_exists('given_name', $this->getFields()))
            return $this->fields['given_name'];

        return null;
    }

    public function getCanonical(): ?string
    {
        $pseudo = sprintf('pseudonymized (%s...)', substr($this->getId(), 0, 8));

        return $this->getName() ?? $this->getShortname() ?? $this->getEmail() ?? $pseudo;
    }

    public function __toString()
    {
        return $this->getCanonical();
    }
}
