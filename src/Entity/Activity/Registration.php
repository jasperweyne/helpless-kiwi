<?php

namespace App\Entity\Activity;

use App\Entity\Person\Person;
use Doctrine\ORM\Mapping as ORM;

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
     */
    private $option;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Person\Person")
     */
    private $person;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Activity\Activity", inversedBy="registrations")
     * @ORM\JoinColumn(name="activity", referencedColumnName="id")
     */
    private $activity;

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

    public function getOption(): ?PriceOption
    {
        return $this->option;
    }

    public function setOption(?PriceOption $option): self
    {
        $this->option = $option;

        return $this;
    }

    public function getPerson(): ?Person
    {
        return $this->person;
    }

    public function setPerson(?Person $person): self
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
}
