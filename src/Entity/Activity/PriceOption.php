<?php

namespace App\Entity\Activity;

use App\Entity\Group\Group;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Overblog\GraphQLBundle\Annotation as GQL;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\OptionRepository")
 * @GQL\Type
 * @GQL\Description("A registration option for an activity.")
 */
class PriceOption
{
    /**
     * @var string | null
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=100, name="title")
     * @Assert\NotBlank
     * @GQL\Field(type="String!")
     * @GQL\Description("The name of the registration option.")
     */
    private $name;

    /**
     * @var Activity | null
     * @ORM\ManyToOne(targetEntity="App\Entity\Activity\Activity", inversedBy="options")
     * @ORM\JoinColumn(name="activity", referencedColumnName="id")
     * @GQL\Field(type="Activity!")
     * @GQL\Description("The activity associated with this registration option.")
     */
    private $activity;

    /**
     * @var Group | null
     * @ORM\ManyToOne(targetEntity="App\Entity\Group\Group")
     * @ORM\JoinColumn(name="target", referencedColumnName="id", nullable=true)
     * @GQL\Field(type="Group")
     * @GQL\Description("The target group of users that can register for this registration option.")
     */
    private $target;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @GQL\Field(type="Int")
     * @GQL\Description("The price of this option, stored in euro-cents.")
     */
    private $price;

    /**
     * TODO what is it's data type?
     *
     * @ORM\Column(type="json")
     */
    private $details;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $confirmationMsg;

    /**
     * @var Collection<int, Registration>
     * @ORM\OneToMany(targetEntity="App\Entity\Activity\Registration", mappedBy="option")
     * @GQL\Field(type="[Registration]")
     * @GQL\Description("The list of registrations for this price option.")
     */
    private $registrations;

    public function __construct()
    {
        $this->registrations = new ArrayCollection();
    }

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

    /**
     * Get name.
     *
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Set name.
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get target.
     *
     * @return Group
     */
    public function getTarget(): ?Group
    {
        return $this->target;
    }

    /**
     * Set target.
     *
     * @param Group $target
     */
    public function setTarget(?Group $target): self
    {
        $this->target = $target;

        return $this;
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
        return $this->activity;
    }

    public function setActivity(?Activity $activity): self
    {
        $this->activity = $activity;

        return $this;
    }

    public function __toString()
    {
        return $this->name.' €'.number_format($this->price / 100, 2, '.', '');
    }

    /**
     * @return Collection<int, Registration>
     */
    public function getRegistrations(): Collection
    {
        return $this->registrations;
    }

    public function addRegistration(Registration $registration): self
    {
        if (!$this->registrations->contains($registration)) {
            $this->registrations[] = $registration;
            $registration->setOption($this);
        }

        return $this;
    }

    public function removeRegistration(Registration $registration): self
    {
        if ($this->registrations->contains($registration)) {
            $this->registrations->removeElement($registration);
            // set the owning side to null (unless already changed)
            if ($registration->getOption() === $this) {
                $registration->setOption(null);
            }
        }

        return $this;
    }
}
