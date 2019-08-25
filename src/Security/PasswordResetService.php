<?php

namespace App\Security;

use App\Entity\Security\Auth;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Encoder\SelfSaltingEncoderInterface;

class PasswordResetService
{
    private $registry;
    private $managerName;

    public function __construct(EntityManagerInterface $em, EncoderFactoryInterface $encoderFactory)
    {
        $this->em = $em;
        $this->encoderFactory = $encoderFactory;
    }

    public function isPasswordRequestTokenValid(Auth $auth, string $token)
    {
        $encoder = $this->encoderFactory->getEncoder($auth);

        $valid = false;
        if ($encoder instanceof SelfSaltingEncoderInterface) {
            $valid = $encoder->isPasswordValid($auth->getPasswordRequestToken(), $token, '');
        } else {
            $valid = $encoder->isPasswordValid($auth->getPasswordRequestToken(), $token, $auth->getPasswordRequestSalt());
        }

        $interval = new \DateTime('24:00');
        $nonExpired = $auth->isPasswordRequestNonExpired($interval->getTimestamp());

        return $valid && $nonExpired;
    }

    public function generatePasswordRequestToken(Auth $auth, bool $persistAndFlush = true)
    {
        $encoder = $this->encoderFactory->getEncoder($auth);
        $token = base64_encode(random_bytes(18));

        $valid = false;
        if ($encoder instanceof SelfSaltingEncoderInterface) {
            $auth->setPasswordRequestToken($encoder->encodePassword($token, ''));
        } else {
            $salt = base64_encode(random_bytes(10));

            $auth->setPasswordRequestSalt($salt);
            $auth->setPasswordRequestToken($encoder->encodePassword($token, $salt));
        }

        $auth->setPasswordRequestedAt(new \DateTime());

        if ($persistAndFlush) {
            $this->em->persist($auth);
            $this->em->flush();
        }

        return $token;
    }

    public function resetPasswordRequestToken(Auth $auth, bool $persistAndFlush = true)
    {
        $encoder = $this->encoderFactory->getEncoder($auth);
        if (!$encoder instanceof SelfSaltingEncoderInterface) {
            $auth->setPasswordRequestSalt(null);
        }

        $auth->setPasswordRequestToken(null);
        $auth->setPasswordRequestedAt(null);

        if ($persistAndFlush) {
            $this->em->persist($auth);
            $this->em->flush();
        }
    }
}
