<?php

namespace App\Entity\Activity;

use App\Entity\Group\Group;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Overblog\GraphQLBundle\Annotation as GQL;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: "App\Repository\OptionRepository")]
#[GQL\Type]
#[GQL\Description('A registration option for an activity.')]
class PriceOption
{
    #[ORM\Id()]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: 'guid')]
    #[ORM\CustomIdGenerator('doctrine.uuid_generator')]
    private ?string $id;

    #[ORM\Column(type: 'string', length: 100, name: 'title')]
    #[Assert\NotBlank]
    #[GQL\Field(type: 'String!')]
    #[GQL\Description('The name of the registration option.')]
    private string $name;

    #[ORM\ManyToOne(targetEntity: "App\Entity\Activity\Activity", inversedBy: 'options')]
    #[ORM\JoinColumn(name: 'activity', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[GQL\Field(type: 'Activity!')]
    #[GQL\Description('The activity associated with this registration option.')]
    private ?Activity $activity = null;

    #[ORM\ManyToOne(targetEntity: "App\Entity\Group\Group")]
    #[ORM\JoinColumn(name: 'target', referencedColumnName: 'id', nullable: true)]
    #[GQL\Field(type: 'Group')]
    #[GQL\Description('The target group of users that can register for this registration option.')]
    private ?Group $target;

    #[ORM\Column(type: 'integer')]
    #[GQL\Field(type: 'Int')]
    #[GQL\Description('The price int of this option, stored in euro-cents.')]
    private int $price;

    /** @var array<string, string> */
    #[ORM\Column(type: 'json')]
    private array $details;

    #[ORM\Column(type: 'string')]
    private string $confirmationMsg;

    /** @var Collection<int, Registration> */
    #[ORM\OneToMany(targetEntity: "App\Entity\Activity\Registration", mappedBy: 'option')]
    #[GQL\Field(type: '[Registration]')]
    #[GQL\Description('The list of registrations for this price option.')]
    private Collection $registrations;

    public function __construct()
    {
        $this->registrations = new ArrayCollection();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getTarget(): ?Group
    {
        return $this->target;
    }

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

    /** @return array<string, string> */
    public function getDetails(): ?array
    {
        return $this->details;
    }

    /** @param array<string, string> $details */
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

    /** @return Collection<int, Registration> */
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
