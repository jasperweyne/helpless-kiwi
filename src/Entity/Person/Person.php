<?php

namespace App\Entity\Person;

use App\Entity\Security\Auth;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PersonRepository")
 */
class Person
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Security\Auth", mappedBy="person")
     */
    private $auth;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $email;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Person\PersonValue", mappedBy="person", orphanRemoval=true)
     */
    private $fieldValues;

    public function __construct()
    {
        $this->fieldValues = new ArrayCollection();
    }

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
     * Get authentication entity.
     *
     * @return string
     */
    public function getAuth(): ?Auth
    {
        return $this->auth;
    }

    /**
     * Set authentication entity.
     *
     * @param Auth $auth
     */
    public function setAuth(?Auth $auth): self
    {
        $this->auth = $auth;

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
    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return Collection|PersonValue[]
     */
    public function getFieldValues(): Collection
    {
        return $this->fieldValues;
    }

    public function addFieldValue(PersonValue $fieldValue): self
    {
        if (!$this->fieldValues->contains($fieldValue)) {
            $this->fieldValues[] = $fieldValue;
            $fieldValue->setPerson($this);
        }

        return $this;
    }

    public function removeFieldValue(PersonValue $fieldValue): self
    {
        if ($this->fieldValues->contains($fieldValue)) {
            $this->fieldValues->removeElement($fieldValue);
            // set the owning side to null (unless already changed)
            if ($fieldValue->getPerson() === $this) {
                $fieldValue->setPerson(null);
            }
        }

        return $this;
    }

    public function getFullname(): ?string
    {
        // Get all items that are part of the name
        $nameFields = $this->getFieldValues()->filter(function ($val) {
            return !is_null($val->getField()->getFullnameOrder());
        });

        // If no name fields, return null
        if (0 == count($nameFields)) {
            return null;
        }

        // Order them
        $nameValues = [];
        foreach ($nameFields as $field) {
            $key = $field->getField()->getFullnameOrder();
            $val = $field->getValue();

            if (!empty($nameValues[$key])) {
                $nameValues[$key] = [];
            }
            $nameValues[$key][] = $val;
        }

        // Build name
        $name = '';
        foreach ($nameValues as $vals) {
            foreach ($vals as $val) {
                $name = $name.$val.' ';
            }
        }

        // Return name
        return trim($name);
    }

    public function getCanonical(): ?string
    {
        $pseudo = sprintf('pseudonymized (%s...)', substr($this->getId(), 0, 8));

        return $this->getFullname() ?? $this->getEmail() ?? $pseudo;
    }

    public function __toString()
    {
        return $this->getCanonical();
    }
}
