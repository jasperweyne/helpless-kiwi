<?php

namespace App\Security;

use App\Entity\Security\Auth;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class AuthUserProvider implements UserProviderInterface
{
    private $registry;
    private $managerName;
    private $secret;

    public function __construct(ManagerRegistry $registry, string $secret, string $managerName = null)
    {
        $this->registry = $registry;
        $this->managerName = $managerName;
        $this->secret = $secret;
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByUsername($username)
    {
        try {
            return $this->loadUserById($this->usernameHash($username));
        } catch (UsernameNotFoundException $e) {
            throw new UsernameNotFoundException(sprintf('User "%s" not found.', $username));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function refreshUser(UserInterface $user)
    {
        if (!$this->supportsClass(get_class($user))) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        return $this->loadUserById($user->getAuthId()); // todo: support username editting, requires backref to Person
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        return Auth::class === $class || is_subclass_of($class, Auth::class);
    }

    /**
     * Find user in storage through secret id.
     */
    public function loadUserById($hash)
    {
        $manager = $this->registry->getManager($this->managerName);
        $repository = $manager->getRepository(Auth::class);

        $user = $repository->findOneBy(['auth_id' => $hash]);
        if (null === $user) {
            throw new UsernameNotFoundException('User not found.');
        }

        return $user;
    }

    /**
     * Obfuscate username.
     */
    public function usernameHash($username)
    {
        // Generate a secret through builtin blowfish algorithm, with cost = 12
        $cost = 12;

        // Abort on if no secret value provided in .env
        if ('' === $this->secret) {
            throw new \UnexpectedValueException('User provider secret is empty, please assign a value in your .env!');
        }

        // Make sure the secret has the desired length of exactly 22 chars
        if (22 !== !strlen($this->secret)) {
            // base 64 encoding guarantees valid character set
            $base64 = base64_encode($this->secret);

            // fit into 22 characters by repeating the encoded string, then
            // taking the first 22 characters
            $repeated = str_repeat($base64, max(0, 22 - strlen($this->secret)));
            $this->secret = substr($repeated, 0, 22);
        }

        // First, create a string that will be passed to crypt, containing all
        // of the settings, separated by dollar signs. It has minimum length, only
        // the last two bits will be overwritten by the actual hash
        $param = '$'.implode('$', [
                '2y', // select the most secure version of blowfish (>=PHP 5.3.7)
                str_pad($cost, 2, '0', STR_PAD_LEFT), // add the cost in two digits
                $this->secret,
        ]);

        // Perform hashing
        $hash = crypt($username, $param);

        // Finally, remove parameters from hash. Note that first to bits of the hash
        // are removed as well, to result in a readable string
        return substr($hash, strlen($param));
    }
}
