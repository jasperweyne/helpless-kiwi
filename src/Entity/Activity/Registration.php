<?php

namespace App\Entity\Activity;

use App\Entity\Order;
use App\Entity\Security\LocalAccount;
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
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Activity\PriceOption", inversedBy="registrations")
     * @ORM\JoinColumn(nullable=false)
     * @GQL\Field(type="PriceOption!")
     * @GQL\Description("The specific registration option of the activity this registration points to.")
     * @Assert\NotBlank
     */
    private $option;

    /**
     * @ORM\ManyToOne(targetEntity=LocalAccount::class, inversedBy="registrations")
     * @ORM\JoinColumn(name="person_id", referencedColumnName="id")
     * @GQL\Field(type="LocalAccount")
     * @GQL\Description("The user that is registered for the activity. Only accessible if the activity is currently visible, or by admins.")
     * @GQL\Access("hasRole('ROLE_ADMIN') or value.getActivity().isVisibleBy(getUser())")
     */
    private $person;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Activity\Activity", inversedBy="registrations")
     * @ORM\JoinColumn(name="activity", referencedColumnName="id")
     * @GQL\Field(type="Activity!")
     * @GQL\Description("The activity for which the user registered.")
     */
    private $activity;

    /**
     * @GQL\Field(type="String")
     * @ORM\Column(type="string", length=255, nullable=true)
     * @GQL\Field(type="String")
     * @GQL\Description("If placed on the reserve list, this value indicates their relative position, by alphabetical ordering.")
     */
    private $reserve_position;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="newdate", type="datetime", nullable=false)
     * @GQL\Field(name="created", type="DateTimeScalar!")
     * @GQL\Description("The date and time the user registered for the activity.")
     */
    private $newdate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="deletedate", type="datetime", nullable=true)
     * @GQL\Field(name="deleted", type="DateTimeScalar")
     * @GQL\Description("The date and time the user deleted their registration for the activity.")
     */
    private $deletedate;

    /**
     * @ORM\Column(name="present", type="boolean", nullable=true)
     * @GQL\Field(type="Boolean")
     * @GQL\Description("Whether the user was present during the activity.")
     */
    private $present;

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

    public function setOption(?PriceOption $option): self
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
        return $this->reserve_position ? Order::create($this->reserve_position) : null;
    }

    public function setReservePosition(?Order $reserve_position): self
    {
        $this->reserve_position = ($reserve_position ? strval($reserve_position) : null);

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

    public function setNewDate(\DateTime $date): self
    {
        $this->newdate = $date;

        return $this;
    }

    /**
     * Get date and time of deregistration.
     *
     * @return DateTime
     */
    public function getDeleteDate()
    {
        return $this->deletedate;
    }

    public function setDeleteDate(\DateTime $date): self
    {
        $this->deletedate = $date;

        return $this;
    }

    public function getPresent()
    {
        return $this->present;
    }

    public function setPresent($present)
    {
        $this->present = $present;
    }
}
