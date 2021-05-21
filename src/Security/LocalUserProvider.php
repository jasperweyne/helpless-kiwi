<?php

namespace App\Security;

use App\Entity\Security\LocalAccount;
use Doctrine\ORM\EntityManagerInterface;
use Drenso\OidcBundle\Security\Authentication\Token\OidcToken;
use Drenso\OidcBundle\Security\UserProvider\OidcUserProviderInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class LocalUserProvider implements UserProviderInterface, OidcUserProviderInterface
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
     * Call this method to create a new user from the data available in the token,
     * but only if the user does not exists yet.
     * If it does exist, return that user.
     *
     * @return UserInterface
     */
    public function loadUserByToken(OidcToken $token)
    {
        $repository = $this->em->getRepository(LocalAccount::class);
        $user = $repository->find($token->getSub());

        // If user does not exist, create it
        if (null === $user) {
            $user = new LocalAccount();
            $user->setId($token->getSub());
            $this->em->persist($user);
        }

        // Update the user data
        $user->setGivenName($token->getGivenName());
        $user->setFamilyName($token->getFamilyName());
        $user->setEmail($token->getEmail());
        $this->em->flush();

        return $user;
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
