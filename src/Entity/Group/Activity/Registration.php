<?php

namespace App\Entity\Group\Activity;

use App\Entity\Group\Relation;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RegistrationRepository")
 */
class Registration extends Relation
{
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

    public function getOption(): ?PriceOption
    {
        return $this->getTaxonomy();
    }

    public function setOption(?PriceOption $option): self
    {
        $this->setTaxonomy($option);

        return $this;
    }

    public function getActivity(): ?Activity
    {
        return $this->getRoot()->getTaxonomy()->getParent();
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
