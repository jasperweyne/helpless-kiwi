<?php

namespace App\Entity\Security;

use App\Entity\Person\Person;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;

/**
 * @ORM\Entity
 */
class Auth implements UserInterface, EquatableInterface
{
    /**
     * @ORM\Id
     * @ORM\OneToOne(targetEntity="App\Entity\Person\Person", inversedBy="auth")
     * @ORM\JoinColumn(name="person", referencedColumnName="id")
     */
    private $person;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $auth_id;

    /**
     * @var string The hashed password
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $password;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="last_login", type="datetime", nullable=true)
     */
    protected $lastLogin;

    /**
     * Encrypted string whose value is sent to the user email address in order to (re-)set the password.
     *
     * @var string
     *
     * @ORM\Column(name="password_request_token", type="string", nullable=true)
     */
    protected $passwordRequestToken;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="password_requested_at", type="datetime", nullable=true)
     */
    protected $passwordRequestedAt;

    /**
     * @var int
     *
     * @ ORM\Column(name="last_sign_in_at", type="datetime", nullable=true)
     */
    protected $lastSignInAt;

    /**
     * @var string
     *
     * @ ORM\Column(name="last_sign_in_ip", type="string", nullable=true)
     */
    protected $lastSignInIp;

    /**
     * getAuthId
     * Insert description here.
     *
     * @return
     *
     * @static
     *
     * @see
     * @since
     */
    public function getAuthId(): string
    {
        return (string) $this->auth_id;
    }

    /**
     * setAuthId
     * Insert description here.
     *
     * @param string
     * @param $auth_id
     *
     * @return
     *
     * @static
     *
     * @see
     * @since
     */
    public function setAuthId(string $auth_id): self
    {
        $this->auth_id = $auth_id;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     * Note that this value isn't loaded by doctrine, but is provided
     * by the parent Person instance.
     */
    public function getUsername(): string
    {
        return $this->getAuthId();
    }

    public function getPerson(): Person
    {
        return $this->person;
    }

    /**
     * setPerson
     * Insert description here.
     *
     * @param string
     * @param $person
     *
     * @return
     *
     * @static
     *
     * @see
     * @since
     */
    public function setPerson(?Person $person): self
    {
        $this->person = $person;

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
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    /**
     * setPassword
     * Insert description here.
     *
     * @param string
     * @param $password
     *
     * @return
     *
     * @static
     *
     * @see
     * @since
     */
    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
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
     * Gets the last login time.
     *
     * @return \DateTime
     */
    public function getLastLogin()
    {
        return $this->lastLogin;
    }

    /**
     * setLastLogin
     * Insert description here.
     *
     * @param \
     * @param DateTime
     * @param $time
     *
     * @return
     *
     * @static
     *
     * @see
     * @since
     */
    public function setLastLogin(\DateTime $time = null)
    {
        $this->lastLogin = $time;

        return $this;
    }

    /**
     * setConfirmationToken
     * Insert description here.
     *
     * @param $passwordRequestToken
     *
     * @return
     *
     * @static
     *
     * @see
     * @since
     */
    public function setPasswordRequestToken($passwordRequestToken)
    {
        $this->passwordRequestToken = $passwordRequestToken;

        return $this;
    }

    /**
     * setPasswordRequestedAt
     * Insert description here.
     *
     * @param \
     * @param DateTime
     * @param $date
     *
     * @return
     *
     * @static
     *
     * @see
     * @since
     */
    public function setPasswordRequestedAt(\DateTime $date = null)
    {
        $this->passwordRequestedAt = $date;

        return $this;
    }

    /**
     * Gets the timestamp that the user requested a password reset.
     *
     * @return \DateTime|null
     */
    public function getPasswordRequestedAt()
    {
        return $this->passwordRequestedAt;
    }

    /**
     * isPasswordRequestNonExpired
     * Insert description here.
     *
     * @param $ttl
     *
     * @return
     *
     * @static
     *
     * @see
     * @since
     */
    public function isPasswordRequestNonExpired($ttl)
    {
        return null === $this->getPasswordRequestedAt() || (
               $this->getPasswordRequestedAt() instanceof \DateTime &&
               $this->getPasswordRequestedAt()->getTimestamp() + $ttl > time());
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

    public function getPasswordRequestToken(): ?string
    {
        return $this->passwordRequestToken;
    }

    public function getPasswordRequestSalt(): ?string
    {
        // not needed
    }

    public function setPasswordRequestSalt(): Auth
    {
        // not needed

        return $this;
    }
}
