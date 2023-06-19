<?php

namespace App\Entity\Mail;

use App\Entity\Security\LocalAccount;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Recipient
{
    /**
     * @var ?string
     */
    #[ORM\Id()]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: 'guid')]
    #[ORM\CustomIdGenerator('doctrine.uuid_generator')]
    private $id;

    /**
     * @var ?LocalAccount
     */
    #[ORM\ManyToOne(targetEntity: "App\Entity\Security\LocalAccount")]
    #[ORM\JoinColumn(name: 'person_id', referencedColumnName: 'id')]
    private $person;

    /**
     * @var ?Mail
     */
    #[ORM\ManyToOne(targetEntity: "App\Entity\Mail\Mail", inversedBy: 'recipients')]
    #[ORM\JoinColumn(name: 'mail', referencedColumnName: 'id')]
    private $mail;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getPerson(): ?LocalAccount
    {
        return $this->person;
    }

    public function setPerson(?LocalAccount $person): self
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
