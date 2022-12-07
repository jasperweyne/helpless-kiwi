<?php

namespace App\Entity\Security;

use App\Repository\ApiTokenRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ApiTokenRepository::class, readOnly: true)]
class ApiToken
{
    #[ORM\Id]
    #[ORM\Column(length: 255)]
    public readonly string $token;

    public function __construct(
        #[ORM\ManyToOne(fetch: 'EAGER')]
        #[ORM\JoinColumn(nullable: false)]
        public readonly LocalAccount $account,
        #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
        private readonly \DateTimeImmutable $expiresAt,
    ) {
        // generate secure 512 bit token (encoded as 88-chars base64-encoded string)
        $this->token = base64_encode(random_bytes(512 / 8));
    }

    public function isValid(\DateTimeInterface $at = new \DateTime('now')): bool
    {
        return $at < $this->expiresAt;
    }
}
