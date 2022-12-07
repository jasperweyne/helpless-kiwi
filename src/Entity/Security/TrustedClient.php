<?php

namespace App\Entity\Security;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class TrustedClient
{
    public function __construct(
        #[ORM\Id]
        #[ORM\Column(length: 255)]
        public readonly string $id,
        #[ORM\Column(length: 255)]
        public readonly string $secret,
    ) {
    }
}
