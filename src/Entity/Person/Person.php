<?php

namespace App\Entity\Person;

use App\Entity\Reference;
use App\Entity\Security\Auth;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PersonRepository")
 */
class Person extends Reference
{
    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Security\Auth", mappedBy="person")
     */
    private $auth;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $firstname;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $lastname;

    /**
     * @var date
     *
     * @ORM\Column(name="birthday", type="date", nullable=true)
     */
    private $birthday;

    /**
     * @ORM\Column(type="array")
     */
    private $labels;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $email;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Location")
     * @ORM\JoinColumn(name="location", referencedColumnName="id")
     */
    private $address;

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
    public function setAuth(Auth $auth): self
    {
        $this->auth = $auth;

        return $this;
    }

    /**
     * Get first name.
     *
     * @return string
     */
    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    /**
     * Set first name.
     *
     * @param string $firstname
     */
    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * Get last name.
     *
     * @return string
     */
    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    /**
     * Set last name.
     *
     * @param string $lastname
     */
    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * Get full name.
     *
     * @return string
     */
    public function getFullname(): ?string
    {
        return $this->getFirstname().' '.$this->getLastname();
    }

    /**
     * Set birthday.
     *
     * @param date $birthday
     */
    public function setBirthday($birthday)
    {
        $this->birthday = $birthday;
    }

    /**
     * Get birthday.
     *
     * @return date
     */
    public function getBirthday()
    {
        return $this->birthday;
    }

    public function getLabels(): ?array
    {
        return $this->labels;
    }

    public function setLabels(?array $labels): self
    {
        $this->labels = $labels;

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

    public function getCanonical(): ?string
    {
        $name = sprintf('pseudonymized (%s...)', substr($this->getName(), 0, 8));

        if (' ' !== $this->getFullname()) {
            $name = $this->getFullname();
        }

        return $name;
    }

    public function getType(): ?string
    {
        return 'Lid'; // ToDo: combine with labels
    }
}
