<?php

namespace App\Entity\Activity;

use App\Entity\Activity\PriceOption;
use App\Entity\Person\Person;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Registration
{
    /**
     * @ORM\Id
     * @ORM\OneToOne(targetEntity="App\Entity\Activity\PriceOption")
     */
    private $option;

    /**
     * @ORM\Id
     * @ORM\OneToOne(targetEntity="App\Entity\Person\Person")
     */
    private $person;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Activity\Activity", inversedBy="registrations")
     * @ORM\JoinColumn(name="activity", referencedColumnName="id")
     */
    private $activity;

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
}
