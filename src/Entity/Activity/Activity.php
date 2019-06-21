<?php

namespace App\Entity\Activity;

use App\Entity\Activity\PriceOption;
use App\Entity\Group\Group;
use App\Entity\Location;
use App\Entity\Reference;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Activity extends Group
{
    /**
     * @ORM\Column(type="string")
     */
    private $description;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Location")
     * @ORM\JoinColumn(name="location", referencedColumnName="id")
     */
    private $location;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Reference")
     * @ORM\JoinColumn(name="primairy_author", referencedColumnName="id")
     */
    private $author;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Activity\PriceOption", mappedBy="activity")
     */
    private $priceOptions;

    /**
     * @ORM\Column(type="datetime")
     */
    private $start;

    /**
     * @ORM\Column(type="datetime")
     */
    private $end;

    /**
     * @ORM\Column(type="datetime")
     */
    private $deadline;

    public function __construct()
    {
        $this->priceOptions = new ArrayCollection();
    }


    /**
     * Get description.
     *
     * @return string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Set description.
     *
     * @param string $description
     */
    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get author.
     *
     * @return Reference
     */
    public function getAuthor(): ?Reference
    {
        return $this->author;
    }

    /**
     * Set author.
     *
     * @param Reference $author
     */
    public function setAuthor(Reference $author): self
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Get price options.
     *
     * @return PriceOption[]
     */
    public function getPriceOptions()//: ?ArrayCollection //todo: type hint
    {
        return $this->priceOptions;
    }

    /**
     * Set price options.
     *
     * @param PriceOption[] $priceOptions
     */
    public function setPriceOptions(array $priceOptions): self
    {
        $this->priceOptions = $priceOptions;

        return $this;
    }

    /**
     * Get start.
     *
     * @return \DateTime
     */
    public function getStart(): ?\DateTime
    {
        return $this->start;
    }

    /**
     * Set start.
     *
     * @param \DateTime $start
     */
    public function setStart(\DateTime $start): self
    {
        $this->start = $start;

        return $this;
    }

    /**
     * Get end.
     *
     * @return \DateTime
     */
    public function getEnd(): ?\DateTime
    {
        return $this->end;
    }

    /**
     * Set id.
     *
     * @param \DateTime $end
     */
    public function setEnd(\DateTime $end): self
    {
        $this->end = $end;

        return $this;
    }

    /**
     * Get deadline.
     *
     * @return \DateTime
     */
    public function getDeadline(): ?\DateTime
    {
        return $this->deadline;
    }

    /**
     * Set id.
     *
     * @param \DateTime $deadline
     */
    public function setDeadline(\DateTime $deadline): self
    {
        $this->deadline = $deadline;

        return $this;
    }

    public function getLocation(): ?Location
    {
        return $this->location;
    }

    public function setLocation(?Location $location): self
    {
        $this->location = $location;

        return $this;
    }

    public function addPriceOption(PriceOption $priceOption): self
    {
        if (!$this->priceOptions->contains($priceOption)) {
            $this->priceOptions[] = $priceOption;
            $priceOption->setActivity($this);
        }

        return $this;
    }

    public function removePriceOption(PriceOption $priceOption): self
    {
        if ($this->priceOptions->contains($priceOption)) {
            $this->priceOptions->removeElement($priceOption);
            // set the owning side to null (unless already changed)
            if ($priceOption->getActivity() === $this) {
                $priceOption->setActivity(null);
            }
        }

        return $this;
    }
}
