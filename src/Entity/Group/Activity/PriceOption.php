<?php

namespace App\Entity\Group\Activity;

use App\Entity\Group\Group;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class PriceOption extends Group
{
    /**
     * @ORM\Column(type="integer")
     */
    private $price;

    /**
     * @ORM\Column(type="json")
     */
    private $details;

    /**
     * @ORM\Column(type="string")
     */
    private $confirmationMsg;

    public function __construct()
    {
        $this->registrations = new ArrayCollection();
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getDetails(): ?array
    {
        return $this->details;
    }

    public function setDetails(array $details): self
    {
        $this->details = $details;

        return $this;
    }

    public function getConfirmationMsg(): ?string
    {
        return $this->confirmationMsg;
    }

    public function setConfirmationMsg(string $confirmationMsg): self
    {
        $this->confirmationMsg = $confirmationMsg;

        return $this;
    }

    public function getActivity(): ?Activity
    {
        return $this->getParent();
    }

    public function setActivity(?Activity $activity): self
    {
        $this->setParent($activity);

        return $this;
    }

    public function __toString()
    {
        return $this->getName().' €'.number_format($this->price / 100, 2, '.', '');
    }

    /**
     * @return Collection|Registration[]
     */
    public function getRegistrations(): Collection
    {
        return $this->children->filter(function ($x) { return $x instanceof Registration; });
    }

    public function addRegistration(Registration $registration): self
    {
        if (!$this->children->contains($registration)) {
            $this->children[] = $registration;
            $registration->setOption($this);
        }

        return $this;
    }

    public function removeRegistration(Registration $registration): self
    {
        if ($this->children->contains($registration)) {
            $this->children->removeElement($registration);
            // set the owning side to null (unless already changed)
            if ($registration->getOption() === $this) {
                $registration->setOption(null);
            }
        }

        return $this;
    }
}
