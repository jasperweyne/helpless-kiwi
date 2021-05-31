<?php

namespace App\Entity\Activity;

use App\Entity\Order;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
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
     * @Assert\NotBlank
     */
    private $option;

    /**
     * @ORM\Column(type="guid")
     */
    private $person_id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Activity\Activity", inversedBy="registrations")
     * @ORM\JoinColumn(name="activity", referencedColumnName="id")
     */
    private $activity;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $reserve_position;

    /**
     * @var date
     *
     * @ORM\Column(name="newdate", type="datetime", nullable=false)
     */
    private $newdate;

    /**
     * @var date
     *
     * @ORM\Column(name="deletedate", type="datetime", nullable=true)
     */
    private $deletedate;

    /**
     * @ORM\Column(name="present", type="boolean", nullable=true)
     */
    private $present;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $comment;

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

    public function getPersonId(): ?string
    {
        return $this->person_id;
    }

    public function setPersonId(?string $person_id): self
    {
        $this->person_id = $person_id;

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

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment)
    {
        $this->comment = $comment;
    }
}
