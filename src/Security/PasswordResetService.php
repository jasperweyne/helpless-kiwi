<?php

namespace App\Security;

use App\Entity\Security\LocalAccount;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;

class PasswordResetService
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var PasswordHasherFactoryInterface
     */
    private $passwordHasher;

    public function __construct(EntityManagerInterface $em, PasswordHasherFactoryInterface $passwordHasher)
    {
        $this->em = $em;
        $this->passwordHasher = $passwordHasher;
    }

    public function isPasswordRequestTokenValid(LocalAccount $auth, string $token): bool
    {
        $encoder = $this->passwordHasher->getPasswordHasher($auth);
        $valid = $encoder->verify($auth->getPasswordRequestToken(), $token);

        $interval = new \DateTime('24:00');
        $nonExpired = $auth->isPasswordRequestNonExpired($interval->getTimestamp());

        return $valid && $nonExpired;
    }

    public function generatePasswordRequestToken(LocalAccount $auth, bool $persistAndFlush = true): string
    {
        $encoder = $this->passwordHasher->getPasswordHasher($auth);
        $token = base64_encode(random_bytes(18));

        $auth->setPasswordRequestToken($encoder->hash($token));
        $auth->setPasswordRequestedAt(new \DateTime());

        if ($persistAndFlush) {
            $this->em->persist($auth);
            $this->em->flush();
        }

        return $token;
    }

    public function resetPasswordRequestToken(LocalAccount $auth, bool $persistAndFlush = true): void
    {
        $auth->setPasswordRequestToken(null);
        $auth->setPasswordRequestedAt(null);

        if ($persistAndFlush) {
            $this->em->persist($auth);
            $this->em->flush();
        }
    }
}
