<?php

namespace App\Entity\Security;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity]
class TrustedClient implements PasswordAuthenticatedUserInterface
{
    /** @var Collection<int, ApiToken> */
    #[ORM\OneToMany(targetEntity: ApiToken::class, mappedBy: 'client', cascade: ['remove'])]
    public Collection $tokens;

    public function __construct(
        #[ORM\Id]
        #[ORM\Column(length: 255)]
        public string $id,
        #[ORM\Column(length: 255)]
        public string $secret
    ) {
        $this->tokens = new ArrayCollection();
    }

    public function getPassword(): ?string
    {
        return $this->secret;
    }
}
