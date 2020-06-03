<?php

namespace App\Entity\Security;

use App\Provider\Person\Person;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;

/**
 * @ORM\Entity
 */
class LocalAccount implements UserInterface, EquatableInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $email;

    /**
     * @var string The hashed password
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $password;

    /**
     * @ORM\Column(type="json")
     */
    private $roles;

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
     * Get id.
     *
     * @return string
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * Set id.
     *
     * @param string $id
     */
    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * getEmail
     * Insert description here.
     *
     * @return
     *
     * @static
     *
     * @see
     * @since
     */
    public function getEmail(): string
    {
        return (string) $this->email;
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
    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     * Note that this value isn't loaded by doctrine, but is provided
     * by the parent Person instance.
     */
    public function getUsername(): string
    {
        return $this->getEmail();
    }

    public function getPerson(): Person
    {
        $person = new Person();
        $person
            ->setId($this->getId())
            ->setEmail($this->getEmail())
            ->setFields(['name' => $this->getEmail()])
        ;

        return $person;
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

        return null;
    }

    public function setPasswordRequestSalt(): LocalAccount
    {
        // not needed

        return $this;
    }
}
