<?php

namespace App\Entity\Activity;

use App\Entity\Order;
use App\Entity\Security\LocalAccount;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Overblog\GraphQLBundle\Annotation as GQL;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @GQL\Type
 * @GQL\Description("A representation of a registration from a user for an activity.")
 * @ORM\Entity(repositoryClass="App\Repository\RegistrationRepository")
 */
class Registration
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Activity\PriceOption", inversedBy="registrations")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @GQL\Field(type="PriceOption!")
     * @GQL\Description("The specific registration option of the activity this registration points to.")
     * @Assert\NotBlank
     *
     * @var PriceOption
     */
    private $option;

    /**
     * @ORM\ManyToOne(targetEntity=LocalAccount::class, inversedBy="registrations")
     * @ORM\JoinColumn(name="person_id", referencedColumnName="id")
     * @GQL\Field(type="LocalAccount")
     * @GQL\Description("The user that is registered for the activity. Only accessible if the activity is currently visible, or by admins.")
     * @GQL\Access("isGranted('ROLE_ADMIN') or value.getActivity().isVisibleBy(getUser())")
     *
     * @var ?LocalAccount
     */
    private $person;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Activity\Activity", inversedBy="registrations")
     * @ORM\JoinColumn(name="activity", referencedColumnName="id", onDelete="CASCADE"))
     * @GQL\Field(type="Activity!")
     * @GQL\Description("The activity for which the user registered.")
     *
     * @var ?Activity
     */
    private $activity;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @GQL\Field(type="String")
     * @GQL\Description("If placed on the reserve list, this value indicates their relative position, by alphabetical ordering.")
     *
     * @var ?string
     */
    private $reserve_position;

    /**
     * @ORM\Column(name="newdate", type="datetime", nullable=false)
     * @GQL\Field(name="created", type="DateTimeScalar!", resolve="@=value.getNewDate()")
     * @GQL\Description("The date and time the user registered for the activity.")
     *
     * @var DateTime
     */
    private $newdate;

    /**
     * @ORM\Column(name="deletedate", type="datetime", nullable=true)
     * @GQL\Field(name="deleted", type="DateTimeScalar", resolve="@=value.getDeleteDate()")
     * @GQL\Description("The date and time the user deleted their registration for the activity.")
     *
     * @var ?DateTime
     */
    private $deletedate;

    /**
     * @ORM\Column(name="present", type="boolean", nullable=true)
     * @GQL\Field(type="Boolean")
     * @GQL\Description("Whether the user was present during the activity.")
     *
     * @var ?bool
     */
    private $present;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     * @var ?string
     */
    private $comment;

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

    public function getPerson(): ?LocalAccount
    {
        return $this->person;
    }

    public function setPerson(?LocalAccount $person): self
    {
        $this->person = $person;

        return $this;
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

    public function isReserve(): bool
    {
        return !\is_null($this->reserve_position);
    }

    public function getReservePosition(): ?Order
    {
        if (!\is_null($this->reserve_position)) {
            return Order::create($this->reserve_position);
        } else {
            return null;
        }
    }

    public function setReservePosition(?Order $reserve_position): self
    {
        if (!\is_null($reserve_position)) {
            $this->reserve_position = strval($reserve_position);
        } else {
            $this->reserve_position = null;
        }

        return $this;
    }

    /**
     * Get date and time of registration.
     *
     * @return DateTime
     */
    public function getNewDate()
    {
        return $this->newdate;
    }

    public function setNewDate(DateTime $date): self
    {
        $this->newdate = $date;

        return $this;
    }

    /**
     * Get date and time of deregistration.
     */
    public function getDeleteDate(): ?DateTime
    {
        return $this->deletedate;
    }

    public function setDeleteDate(DateTime $date): self
    {
        $this->deletedate = $date;

        return $this;
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

    public function __construct()
    {
        $this->newdate = new DateTime('now');
    }
}
