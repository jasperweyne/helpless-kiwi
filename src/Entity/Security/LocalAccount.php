<?php

namespace App\Entity\Security;

use App\Entity\Activity\Registration;
use App\Entity\Group\Group;
use App\Entity\Group\Relation;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Overblog\GraphQLBundle\Annotation as GQL;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity
 * @GQL\Type
 * @GQL\Description("A registered user who can log in and register for activities.")
 */
class LocalAccount implements UserInterface, PasswordAuthenticatedUserInterface, EquatableInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     *
     * @var ?string
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     * @GQL\Field(type="String")
     * @GQL\Description("The e-mail address of the user.")
     * @GQL\Access("isGranted('ROLE_ADMIN') or value == getUser()")
     *
     * @var string
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=180)
     * @GQL\Field(type="String")
     * @GQL\Description("The given name of the user (the first name in western cultures).")
     * @GQL\Access("isAuthenticated()")
     *
     * @var string
     */
    private $givenName;

    /**
     * @ORM\Column(type="string", length=180)
     * @GQL\Field(type="String")
     * @GQL\Description("The family name of the user (the last name in western cultures).")
     * @GQL\Access("isAuthenticated()")
     *
     * @var string
     */
    private $familyName;

    /**
     * The hashed password.
     *
     * @ORM\Column(type="string", nullable=true)
     *
     * @var ?string
     */
    private $password;

    /**
     * The OpenID Connect subject claim value.
     *
     * @ORM\Column(type="string", length=255, nullable=true, unique=true)
     *
     * @var ?string
     */
    private $oidc;

    /**
     * @ORM\Column(type="json")
     *
     * @var string[]
     */
    private $roles;

    /**
     * Encrypted string whose value is sent to the user email address in order to (re-)set the password.
     *
     * @ORM\Column(name="password_request_token", type="string", nullable=true)
     *
     * @var ?string
     */
    protected $passwordRequestToken;

    /**
     * @ORM\Column(name="password_requested_at", type="datetime", nullable=true)
     *
     * @var ?DateTime
     */
    protected $passwordRequestedAt;

    /**
     * @ORM\OneToMany(targetEntity=Registration::class, mappedBy="person")
     * @GQL\Field(type="[Registration]")
     * @GQL\Description("All activity registrations for the user.")
     * @GQL\Access("isGranted('ROLE_ADMIN') or value == getUser()")
     *
     * @var Collection<int, Registration>
     */
    private $registrations;

    /**
     * @ORM\OneToMany(targetEntity=Relation::class, mappedBy="person")
     * @GQL\Field(type="[Relation]")
     * @GQL\Description("All group membership relations for the user.")
     * @GQL\Access("isGranted('ROLE_ADMIN') or value == getUser()")
     *
     * @var Collection<int, Relation>
     */
    private $relations;

    /**
     * Get id.
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * Set id.
     */
    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

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
    public function getUsername(): ?string
    {
        return $this->getEmail();
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->getUsername();
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName(): ?string
    {
        $name = \trim($this->getGivenName().' '.$this->getFamilyName());

        return '' != $name ? $name : null;
    }

    public function setName(string $name): self
    {
        $this->setFamilyName('');
        $this->setGivenName($name);

        return $this;
    }

    public function getGivenName(): ?string
    {
        return $this->givenName;
    }

    public function setGivenName(string $givenName): self
    {
        $this->givenName = $givenName;

        return $this;
    }

    public function getFamilyName(): ?string
    {
        return $this->familyName;
    }

    public function setFamilyName(string $familyName): self
    {
        $this->familyName = $familyName;

        return $this;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        if (count($this->getActiveGroups()) > 0) {
            $roles[] = 'ROLE_AUTHOR';
        }

        return array_unique($roles);
    }

    /**
     * @param string[] $roles
     */
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @GQL\Field(type="Boolean!")
     * @GQL\Description("Whether this user is an administrator.")
     * @GQL\Access("isAuthenticated()")
     */
    public function isAdmin(): bool
    {
        return in_array('ROLE_ADMIN', $this->getRoles(), true);
    }

    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getSalt(): ?string
    {
        // not needed when using the "bcrypt" algorithm in security.yaml

        return null;
    }

    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * Get OpenID Connect subject claim.
     */
    public function getOidc(): ?string
    {
        return $this->oidc;
    }

    /**
     * Set the OpenID Connect subject claim.
     */
    public function setOidc(?string $sub): self
    {
        $this->oidc = $sub;

        return $this;
    }

    public function setPasswordRequestToken(?string $passwordRequestToken): self
    {
        $this->passwordRequestToken = $passwordRequestToken;

        return $this;
    }

    public function setPasswordRequestedAt(DateTime $date = null): self
    {
        $this->passwordRequestedAt = $date;

        return $this;
    }

    /**
     * Gets the timestamp that the user requested a password reset.
     *
     * @return Datetime | null
     */
    public function getPasswordRequestedAt()
    {
        return $this->passwordRequestedAt;
    }

    public function isPasswordRequestNonExpired(int $ttl): bool
    {
        return null === $this->getPasswordRequestedAt() || (
            $this->getPasswordRequestedAt()->getTimestamp() + $ttl > time()
        );
    }

    public function isEqualTo(UserInterface $user): bool
    {
        return $this->getUserIdentifier() === $user->getUserIdentifier();
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

    public function setPasswordRequestSalt(?string $salt = null): LocalAccount
    {
        // not needed

        return $this;
    }

    public function getCanonical(): string
    {
        $pseudo = sprintf('pseudonymized (%s...)', substr($this->getId(), 0, 8));

        return $this->getName() ?: $this->getEmail() ?: $pseudo;
    }

    public function __toString()
    {
        return $this->getCanonical();
    }

    public function __construct()
    {
        $this->roles = [];
        $this->registrations = new ArrayCollection();
        $this->relations = new ArrayCollection();
    }

    /**
     * @return Collection<int, Registration> | null
     */
    public function getRegistrations()
    {
        return $this->registrations;
    }

    public function addRegistration(Registration $registration): self
    {
        if (!$this->registrations->contains($registration)) {
            $this->registrations[] = $registration;
            $registration->setPerson($this);
        }

        return $this;
    }

    public function removeRegistration(Registration $registration): self
    {
        if ($this->registrations->removeElement($registration)) {
            // set the owning side to null (unless already changed)
            if ($registration->getPerson() === $this) {
                $registration->setPerson(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Relation>
     */
    public function getRelations()
    {
        return $this->relations;
    }

    public function addRelation(Relation $relation): self
    {
        if (!$this->relations->contains($relation)) {
            $this->relations[] = $relation;
            $relation->setPerson($this);
        }

        return $this;
    }

    public function removeRelation(Relation $relation): self
    {
        if ($this->relations->removeElement($relation)) {
            // set the owning side to null (unless already changed)
            if ($relation->getPerson() === $this) {
                $relation->setPerson(null);
            }
        }

        return $this;
    }

    /**
     * @return Group[]
     */
    public function getActiveGroups(): array
    {
        $groups = [];
        foreach ($this->getRelations() as $relation) {
            if (null !== $relation->getGroup() && true === $relation->getGroup()->isActive()) {
                $groups[] = $relation->getGroup();
            }
        }

        return $groups;
    }
}
