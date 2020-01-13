<?php

namespace App\Entity\Mail;

use App\Entity\Person\Person;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Recipient
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Person\Person")
     */
    private $person;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Mail\Mail", inversedBy="recipients")
     * @ORM\JoinColumn(name="mail", referencedColumnName="id")
     */
    private $mail;

    public function getId(): ?string
    {
        return $this->id;
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

    public function getMail(): ?Mail
    {
        return $this->mail;
    }

    public function setMail(?Mail $mail): self
    {
        $this->mail = $mail;

        return $this;
    }
}
