<?php

namespace App\Entity\Security;

use App\Entity\Activity\Registration;
use App\Entity\Group\Group;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Overblog\GraphQLBundle\Annotation as GQL;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity]
#[UniqueEntity(
    fields: 'email',
    message: 'This e-mail address is already in use.'
)]
#[UniqueEntity(
    fields: 'oidc',
    message: 'This OpenID Connect sub is already in use.'
)]
#[GQL\Type]
#[GQL\Description('A registered user who can log in and register for activities.')]
class LocalAccount implements UserInterface, PasswordAuthenticatedUserInterface, EquatableInterface, ContactInterface
{
    #[ORM\Id()]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: 'guid')]
    #[ORM\CustomIdGenerator('doctrine.uuid_generator')]
    private ?string $id;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    #[GQL\Field(type: 'String')]
    #[GQL\Description('The e-mail address of the user.')]
    #[GQL\Access("isGranted('ROLE_ADMIN') or value == getUser()")]
    private ?string $email = null;

    #[ORM\Column(type: 'string', length: 180)]
    #[GQL\Field(type: 'String')]
    #[GQL\Description('The given name of the user (the first name in western cultures).')]
    #[GQL\Access('isAuthenticated()')]
    private ?string $givenName = null;

    #[ORM\Column(type: 'string', length: 180)]
    #[GQL\Field(type: 'String')]
    #[GQL\Description('The family name of the user (the last name in western cultures).')]
    #[GQL\Access('isAuthenticated()')]
    private ?string $familyName = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $password = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true, unique: true)]
    private ?string $oidc = null;

    /** @var string[] */
    #[ORM\Column(type: 'json')]
    private array $roles;

    #[ORM\Column(name: 'calendar_token', type: 'string')]
    private string $calendarToken;

    /** Encrypted string whose value is sent to the user email address in order to (re-)set the password. */
    #[ORM\Column(name: 'password_request_token', type: 'string', nullable: true)]
    protected ?string $passwordRequestToken;

    #[ORM\Column(name: 'password_requested_at', type: 'datetime', nullable: true)]
    protected ?\DateTime $passwordRequestedAt;

    /** @var Collection<int, Registration> */
    #[ORM\OneToMany(targetEntity: Registration::class, mappedBy: 'person')]
    #[GQL\Field(type: '[Registration]')]
    #[GQL\Description('All activity registrations for the user.')]
    #[GQL\Access("isGranted('ROLE_ADMIN') or value == getUser()")]
    private Collection $registrations;

    /** @var Collection<int, Group> */
    #[ORM\ManyToMany(targetEntity: Group::class, inversedBy: 'relations')]
    #[ORM\JoinTable('relation')]
    #[ORM\JoinColumn('person_id', onDelete: 'CASCADE')]
    #[GQL\Field(type: '[Group]')]
    #[GQL\Description('All group memberships for the user.')]
    #[GQL\Access("isGranted('ROLE_ADMIN') or value == getUser()")]
    private Collection $relations;

    public function getId(): ?string
    {
        return $this->id;
    }

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

    /** @param string[] $roles */
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function getCalendarToken(): ?string
    {
        return $this->calendarToken;
    }

    public function renewCalendarToken(): self
    {
        $this->calendarToken = bin2hex(random_bytes(16));

        return $this;
    }

    #[GQL\Field(type: 'Boolean!')]
    #[GQL\Description('Whether this user is an administrator.')]
    #[GQL\Access('isAuthenticated()')]
    public function isAdmin(): bool
    {
        return in_array('ROLE_ADMIN', $this->getRoles(), true);
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): self
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

    public function getOidc(): ?string
    {
        return $this->oidc;
    }

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

    public function setPasswordRequestedAt(?\DateTime $date = null): self
    {
        $this->passwordRequestedAt = $date;

        return $this;
    }

    public function getPasswordRequestedAt(): ?\DateTime
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
        assert(is_string($this->getId()));
        if (null !== $name = $this->getName()) {
            return $name;
        } elseif (null !== $mail = $this->getEmail()) {
            return $mail;
        } else {
            return sprintf('pseudonymized (%s...)', substr($this->getId(), 0, 8));
        }
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
        $this->renewCalendarToken();
    }

    /** @return Collection<int, Registration>|null */
    public function getRegistrations(): ?Collection
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

    /** @return Collection<int, Group> */
    public function getRelations(): Collection
    {
        return $this->relations;
    }

    public function addRelation(Group $relation): self
    {
        if (!$this->relations->contains($relation)) {
            $this->relations->add($relation);
        }

        return $this;
    }

    public function removeRelation(Group $relation): self
    {
        $this->relations->removeElement($relation);

        return $this;
    }

    /** @return Group[] */
    public function getActiveGroups(): array
    {
        return $this->getRelations()->filter(fn (Group $group) => $group->isActive() ?? false)->toArray();
    }
}
