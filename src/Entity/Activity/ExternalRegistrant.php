<?php

namespace App\Entity\Activity;

use App\Entity\Security\ContactInterface;
use Doctrine\ORM\Mapping as ORM;
use Overblog\GraphQLBundle\Annotation as GQL;

#[ORM\Embeddable]
#[GQL\Type]
#[GQL\Description('A registrant for an activity.')]
class ExternalRegistrant implements ContactInterface
{
    #[ORM\Column(type: 'string', length: 180, nullable: true)]
    private ?string $email;

    #[ORM\Column(type: 'string', length: 180, nullable: true)]
    private ?string $name;

    #[GQL\Field(type: 'String')]
    #[GQL\Description('The e-mail address of the registrant.')]
    #[GQL\Access("hasRole('ROLE_ADMIN')")]
    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    #[GQL\Field(type: 'String')]
    #[GQL\Description('The full name of the registrant.')]
    #[GQL\Access("hasRole('ROLE_ADMIN')")]
    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getCanonical(): string
    {
        return $this->name ?? $this->email ?? 'Unknown registrant';
    }

    public function __toString()
    {
        return $this->getCanonical();
    }
}
