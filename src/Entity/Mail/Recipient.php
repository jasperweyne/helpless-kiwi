<?php

namespace App\Entity\Mail;

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
     * @ORM\Column(type="guid")
     */
    private $person_id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Mail\Mail", inversedBy="recipients")
     * @ORM\JoinColumn(name="mail", referencedColumnName="id")
     */
    private $mail;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getPersonId(): ?string
    {
        return $this->person_id;
    }

    public function setPersonId(?string $person_id): self
    {
        $this->person_id = $person_id;

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
