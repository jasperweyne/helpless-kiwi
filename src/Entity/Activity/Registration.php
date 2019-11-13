<?php

namespace App\Entity\Activity;

use App\Entity\Person\Person;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
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
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $reserve_position;

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
}
