<?php

namespace App\Entity\Activity;

use App\Entity\Security\ContactInterface;
use App\Entity\Security\LocalAccount;
use Doctrine\ORM\Mapping as ORM;
use Overblog\GraphQLBundle\Annotation as GQL;
use Symfony\Component\Validator\Constraints as Assert;

#[GQL\Type]
#[GQL\Description('A representation of a registration from a user for an activity.')]
#[ORM\Entity(repositoryClass: "App\Repository\RegistrationRepository")]
class Registration
{
    #[ORM\Id()]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: 'guid')]
    #[ORM\CustomIdGenerator('doctrine.uuid_generator')]
    private ?string $id;

    #[ORM\ManyToOne(targetEntity: "App\Entity\Activity\PriceOption", inversedBy: 'registrations')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[GQL\Field(type: 'PriceOption!')]
    #[GQL\Description('The specific registration option of the activity this registration points to.')]
    #[Assert\NotBlank]
    private ?PriceOption $option = null;

    #[ORM\ManyToOne(targetEntity: LocalAccount::class, inversedBy: 'registrations')]
    #[ORM\JoinColumn(name: 'person_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    #[GQL\Field(type: 'LocalAccount')]
    #[GQL\Description('The user that is registered for the activity. Only accessible if the activity is currently visible, or by admins.')]
    #[GQL\Access("isGranted('ROLE_ADMIN') or value.getActivity().isVisibleBy(getUser())")]
    private ?LocalAccount $person;

    #[ORM\ManyToOne(targetEntity: "App\Entity\Activity\Activity", inversedBy: 'registrations')]
    #[ORM\JoinColumn(name: 'activity', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[GQL\Field(type: 'Activity!')]
    #[GQL\Description('The activity for which the user registered.')]
    private ?Activity $activity = null;

    #[ORM\Column(name: 'newdate', type: 'datetime', nullable: false)]
    #[GQL\Field(name: 'created', type: 'DateTimeScalar!', resolve: '@=value.getNewDate()')]
    #[GQL\Description('The date and time the user registered for the activity.')]
    private \DateTime $newdate;

    #[ORM\Column(name: 'deletedate', type: 'datetime', nullable: true)]
    #[GQL\Field(name: 'deleted', type: 'DateTimeScalar', resolve: '@=value.getDeleteDate()')]
    #[GQL\Description('The date and time the user deleted their registration for the activity.')]
    private ?\DateTime $deletedate = null;

    #[ORM\Column(name: 'present', type: 'boolean', nullable: true)]
    #[GQL\Field(type: 'Boolean')]
    #[GQL\Description('Whether the user was present during the activity.')]
    private ?bool $present;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $comment;

    #[ORM\Embedded(class: ExternalRegistrant::class)]
    private ?ExternalRegistrant $externalPerson;

    #[ORM\Column(name: 'transferable', type: 'datetime', nullable: true)]
    #[GQL\Field(type: 'DateTimeScalar')]
    #[GQL\Description('Whether this registration is available for transfer to another user.')]
    private ?\DateTime $transferable = null;

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

    public function getOption(): ?PriceOption
    {
        return $this->option;
    }

    public function setOption(PriceOption $option): self
    {
        $this->option = $option;

        return $this;
    }

    public function getPerson(): ?ContactInterface
    {
        return $this->person ?? $this->externalPerson;
    }

    public function setPerson(?ContactInterface $person): self
    {
        $this->person = null;
        $this->externalPerson = null;

        if ($person instanceof LocalAccount) {
            $this->person = $person;
        } elseif ($person instanceof ExternalRegistrant) {
            $this->externalPerson = $person;
        }

        return $this;
    }

    public function isExternal(): bool
    {
        return $this->getPerson() instanceof ExternalRegistrant;
    }

    public function getActivity(): ?Activity
    {
        return $this->activity;
    }

    public function setActivity(?Activity $activity): self
    {
        $this->activity = $activity;

        return $this;
    }

    public function getNewDate(): \DateTime
    {
        return $this->newdate;
    }

    public function setNewDate(\DateTime $date): self
    {
        $this->newdate = $date;

        return $this;
    }

    public function getDeleteDate(): ?\DateTime
    {
        return $this->deletedate;
    }

    public function setDeleteDate(\DateTime $date): self
    {
        $this->deletedate = $date;

        return $this;
    }

    public function isDeleted(): bool
    {
        return !\is_null($this->getDeleteDate());
    }

    public function getPresent(): ?bool
    {
        return $this->present;
    }

    public function setPresent(?bool $present): void
    {
        $this->present = $present;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): void
    {
        $this->comment = $comment;
    }

    public function isTransferable(): ?\DateTime
    {
        return $this->transferable;
    }

    public function setTransferable(?\DateTime $transferable): self
    {
        $this->transferable = $transferable;

        return $this;
    }

    public function __construct()
    {
        $this->newdate = new \DateTime('now');
    }
}
