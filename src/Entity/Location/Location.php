<?php

namespace App\Entity\Location;

use App\Entity\Activity\Activity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Overblog\GraphQLBundle\Annotation as GQL;

#[ORM\Entity]
#[GQL\Type]
#[GQL\Description("A physical location where activities are organized.")]
class Location
{
    /**
     * @var ?string
     */
    #[ORM\Id()]
    #[ORM\GeneratedValue(strategy: "UUID")]
    #[ORM\Column(type: "guid")]
    private $id;

    /**
     * @var string
     */
    #[ORM\Column(type: "string")]
    #[GQL\Field(type: "String")]
    #[GQL\Description("The address of the location.")]
    private $address;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Activity\Activity", mappedBy="location")
     * @GQL\Field(type="[Activity]")
     * @GQL\Description("The activities that have taken place at this location.")
     *
     * @var Collection<int, Activity>
     */
    private $activities;

    public function __construct()
    {
        $this->activities = new ArrayCollection();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     *  DONT USE THIS METHOD
     *  This method only exists to please Alice.
     *
     *  We're returning a void so that if someone accidentally used this method
     *  they'll observe unexpected behaviour
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): self
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get activities.
     *
     * @return Collection<int, Activity>
     */
    public function getActivities(): Collection
    {
        return $this->activities;
    }

    public function addActivity(Activity $activity): self
    {
        if (!$this->activities->contains($activity)) {
            $this->activities[] = $activity;
            $activity->setLocation($this);
        }

        return $this;
    }

    public function removeActivity(Activity $activity): self
    {
        if ($this->activities->contains($activity)) {
            $this->activities->removeElement($activity);
            // set the owning side to null (unless already changed)
            if ($activity->getLocation() === $this) {
                $activity->setLocation(null);
            }
        }

        return $this;
    }
}
