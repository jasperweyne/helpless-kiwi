<?php

namespace App\Entity\Security;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity
 */
class OAuth2User implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="string", unique=true)
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     */
    private $refreshToken;

    private $roles;

    /**
     * getId
     * Insert description here.
     *
     * @return
     *
     * @static
     *
     * @see
     * @since
     */
    public function getId(): string
    {
        return (string) $this->id;
    }

    public function getUsername()
    {
        return $this->getId();
    }

    /**
     * setId
     * Insert description here.
     *
     * @param string
     * @param $id
     *
     * @return
     *
     * @static
     *
     * @see
     * @since
     */
    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getRefreshToken(): string
    {
        return (string) $this->refreshToken;
    }

    /**
     * setRefreshToken
     * Insert description here.
     *
     * @param string
     * @param $refreshToken
     *
     * @return
     *
     * @static
     *
     * @see
     * @since
     */
    public function setRefreshToken(string $refreshToken): self
    {
        $this->refreshToken = $refreshToken;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * setRoles
     * Insert description here.
     *
     * @param array
     * @param $roles
     *
     * @return
     *
     * @static
     *
     * @see
     * @since
     */
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword()
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * isEqualTo
     * Insert description here.
     *
     * @param UserInterface
     * @param $user
     *
     * @return
     *
     * @static
     *
     * @see
     * @since
     */
    public function isEqualTo(UserInterface $user)
    {
        return $this->getUsername() === $user->getUsername();
    }
}
