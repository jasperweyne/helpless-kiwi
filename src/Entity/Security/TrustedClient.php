<?php

namespace App\Entity\Security;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class TrustedClient
{
    /**
     * @var Collection<int, ApiToken>
     */
    #[ORM\OneToMany(targetEntity: ApiToken::class, mappedBy: 'client', cascade: ['remove'])]
    public readonly Collection $tokens;

    public function __construct(
        #[ORM\Id]
        #[ORM\Column(length: 255)]
        public readonly string $id,
        #[ORM\Column(length: 255)]
        public readonly string $secret
    ) {
        $this->tokens = new ArrayCollection();
    }
}
