<?php

namespace App\Entity\Location;

use App\Entity\Activity\Activity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Overblog\GraphQLBundle\Annotation as GQL;

/**
 * @ORM\Entity
 * @GQL\Type
 * @GQL\Description("A physical location where activities are organized.")
 */
class Location
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
     * @ORM\Column(type="string")
     * @GQL\Field(type="String")
     * @GQL\Description("The address of the location.")
     *
     * @var string
     */
    private $address;

    /**
     * @GQL\Field(type="[Activity]")
     * @GQL\Description("All activities whose destination as this location.")
     * @ORM\OneToMany(targetEntity="App\Entity\Activity\Activity", mappedBy="location")
     *
     * @var Collection<int, Activity>
     */
    private $activities;

    /**
     * @GQL\Field(type="[Note]")
     * @GQL\Description("All notes and commits about this location.")
     * @ORM\OneToMany(targetEntity=Note::class, mappedBy="location")
     */
    private $notes;

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

    /**
     * @return Collection<int, Note>
     */
    public function getNotes(): Collection
    {
        return $this->notes;
    }

    public function addNote(Note $note): self
    {
        if (!$this->notes->contains($note)) {
            $this->notes[] = $note;
            $note->setLocation($this);
        }

        return $this;
    }

    public function removeNote(Note $note): self
    {
        if ($this->notes->removeElement($note)) {
            // set the owning side to null (unless already changed)
            if ($note->getLocation() === $this) {
                $note->setLocation(null);
            }
        }

        return $this;
    }

    public function __construct()
    {
        $this->activities = new ArrayCollection();
        $this->notes = new ArrayCollection();
    }
}
