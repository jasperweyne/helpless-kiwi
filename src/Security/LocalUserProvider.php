<?php

namespace App\Security;

use App\Entity\Security\LocalAccount;
use Doctrine\ORM\EntityManagerInterface;
use Drenso\OidcBundle\Model\OidcUserData;
use Drenso\OidcBundle\Security\Exception\OidcUserNotFoundException;
use Drenso\OidcBundle\Security\UserProvider\OidcUserProviderInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class LocalUserProvider implements UserProviderInterface, OidcUserProviderInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$this->supportsClass(get_class($user))) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass(string $class): bool
    {
        return LocalAccount::class === $class || is_subclass_of($class, LocalAccount::class);
    }

    public function ensureUserExists(string $userIdentifier, OidcUserData $token): void
    {
        $repository = $this->em->getRepository(LocalAccount::class);
        $user = $repository->findOneBy(['oidc' => $userIdentifier]);

        // If user does not exist, create it
        if (null === $user) {
            $user = new LocalAccount();
            $user->setOidc($userIdentifier);
            $user->setRoles([]);
            $this->em->persist($user);
        }

        // Update the user data
        $user->setName($token->getFullName());
        $user->setGivenName($token->getGivenName());
        $user->setFamilyName($token->getFamilyName());
        $user->setEmail($token->getEmail());
        $this->em->flush();
    }

    public function loadOidcUser(string $userIdentifier): UserInterface
    {
        $repository = $this->em->getRepository(LocalAccount::class);
        $user = $repository->findOneBy(['oidc' => $userIdentifier]);

        if ($user === null) {
            throw new OidcUserNotFoundException("$userIdentifier is unknown");
        }

        return $user;
    }

    public function loadUserByUsername(string $username): UserInterface
    {
        return $this->loadUserByIdentifier($username);
    }

    /**
     * Find user in storage through secret id.
     */
    public function loadUserByIdentifier(string $email): UserInterface
    {
        $repository = $this->em->getRepository(LocalAccount::class);

        $user = $repository->findOneBy(['email' => $email]);
        if (null === $user) {
            $excep = new UserNotFoundException('User not found.');
            $excep->setUserIdentifier($email);
            throw $excep;
        }

        return $user;
    }
}
