<?php

namespace App\Entity\Mail;

use App\Entity\Security\LocalAccount;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Mail
{
    #[ORM\Id()]
    #[ORM\GeneratedValue(strategy: 'UUID')]
    #[ORM\Column(type: 'guid')]
    private ?string $id;

    #[ORM\ManyToOne(targetEntity: "App\Entity\Security\LocalAccount")]
    #[ORM\JoinColumn(name: 'person_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    private ?LocalAccount $person = null;

    /** @var Collection<int,Recipient> */
    #[ORM\OneToMany(targetEntity: "App\Entity\Mail\Recipient", mappedBy: 'mail')]
    private Collection $recipients;

    #[ORM\Column(type: 'string')]
    private string $title;

    #[ORM\Column(type: 'text')]
    private string $content;

    #[ORM\Column(type: 'string', length: 255)]
    private string $sender;

    #[ORM\Column(type: 'datetime')]
    private \DateTime $sentAt;

    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     *  DONT USE THIS METHOD
     *  This method only exists to please Alice.
     *
     *  We're returning a void so that if someone accidentally used this method
     *  they'll observe unexpected behaviour
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
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

    /** @return Collection<int,Recipient> */
    public function getRecipients(): Collection
    {
        return $this->recipients;
    }

    public function addRecipient(Recipient $recipient): self
    {
        if (!$this->recipients->contains($recipient)) {
            $this->recipients[] = $recipient;
            $recipient->setMail($this);
        }

        return $this;
    }

    public function removeRecipient(Recipient $recipient): self
    {
        if ($this->recipients->contains($recipient)) {
            $this->recipients->removeElement($recipient);
            // set the owning side to null (unless already changed)
            if ($recipient->getMail() === $this) {
                $recipient->setMail(null);
            }
        }

        return $this;
    }

    public function getSender(): ?string
    {
        return $this->sender;
    }

    public function setSender(string $sender): self
    {
        $this->sender = $sender;

        return $this;
    }

    public function getSentAt(): ?\DateTime
    {
        return $this->sentAt;
    }

    public function setSentAt(\DateTime $sentAt): self
    {
        $this->sentAt = $sentAt;

        return $this;
    }
}
