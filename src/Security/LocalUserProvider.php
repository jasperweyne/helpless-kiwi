<?php

namespace App\Security;

use App\Entity\Security\LocalAccount;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class LocalUserProvider implements UserProviderInterface
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function refreshUser(UserInterface $user)
    {
        if (!$this->supportsClass(get_class($user))) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        return LocalAccount::class === $class || is_subclass_of($class, LocalAccount::class);
    }

    /**
     * Find user in storage through secret id.
     */
    public function loadUserByUsername($email)
    {
        $repository = $this->em->getRepository(LocalAccount::class);

        $user = $repository->findOneBy(['email' => $email]);
        if (null === $user) {
            $excep = new UsernameNotFoundException('User not found.');
            $excep->setUsername($email);
            throw $excep;
        }

        return $user;
    }
}
