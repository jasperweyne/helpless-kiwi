<?php

namespace App\Entity\Activity;

use App\Entity\Group\Group;
use App\Entity\Location\Location;
use App\Entity\Security\LocalAccount;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Overblog\GraphQLBundle\Annotation as GQL;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Entity\File as EmbeddedFile;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[GQL\Type]
#[GQL\Description('Information on a physical activity that users can register themselves to.')]
#[ORM\Entity(repositoryClass: "App\Repository\ActivityRepository")]
#[Vich\Uploadable]
class Activity
{
    #[ORM\Id()]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: 'guid')]
    #[ORM\CustomIdGenerator('doctrine.uuid_generator')]
    private ?string $id;

    #[ORM\Column(type: 'string', length: 100, name: 'title')]
    #[Assert\NotBlank]
    #[GQL\Field(type: 'String!')]
    #[GQL\Description('The name of the activity.')]
    private string $name;

    #[ORM\Column(name: 'archived', type: 'boolean', options: ['default' => false])]
    #[GQL\Field(type: 'Boolean!')]
    #[GQL\Description('If this activity is archived')]
    private bool $archived = false;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank]
    #[GQL\Field(type: 'String!')]
    #[GQL\Description('A textual description of the activity.')]
    private string $description;

    /** @var Collection<int, PriceOption> */
    #[ORM\OneToMany(targetEntity: "App\Entity\Activity\PriceOption", mappedBy: 'activity')]
    #[GQL\Field(type: '[PriceOption]')]
    #[GQL\Description('The available registration options for the activity.')]
    private Collection $options;

    /** @var Collection<int, Registration> */
    #[GQL\Field(type: '[Registration]')]
    #[GQL\Description('All registrations stored for this activity, regardless of option.')]
    #[ORM\OneToMany(targetEntity: "App\Entity\Activity\Registration", mappedBy: 'activity')]
    private Collection $registrations;

    #[ORM\OneToOne(targetEntity: "App\Entity\Location\Location")]
    #[ORM\JoinColumn(name: 'location', referencedColumnName: 'id')]
    #[GQL\Field(type: 'Location!')]
    #[GQL\Description('The (physical) location of the activity.')]
    #[Assert\NotBlank]
    private ?Location $location;

    #[ORM\ManyToOne(targetEntity: "App\Entity\Group\Group")]
    #[ORM\JoinColumn(name: 'primairy_author', referencedColumnName: 'id', nullable: true)]
    #[GQL\Field(type: 'Group')]
    #[GQL\Description('The group that authored this activity.')]
    private ?Group $author = null;

    #[GQL\Field(type: 'Group')]
    #[GQL\Description('The group of all users that can see and register to this activity.')]
    #[ORM\ManyToOne(targetEntity: "App\Entity\Group\Group")]
    #[ORM\JoinColumn(name: 'target', referencedColumnName: 'id', nullable: true)]
    private ?Group $target;

    #[ORM\Column(type: 'string')]
    #[GQL\Field(type: 'String!')]
    #[GQL\Description('The color associated with this activity, stored for presentation purposes.')]
    #[Assert\NotBlank]
    private string $color;

    #[ORM\Column(type: 'datetime')]
    #[GQL\Field(type: 'DateTimeScalar!')]
    #[GQL\Description('The date and time the activity starts.')]
    #[Assert\NotBlank]
    private ?\DateTime $start = null;

    #[ORM\Column(type: 'datetime')]
    #[GQL\Field(type: 'DateTimeScalar!')]
    #[GQL\Description('The date and time the activity ends.')]
    #[Assert\NotBlank]
    #[Assert\Expression('value >= this.getStart()', message: 'Een activiteit kan niet eindigen voor de start.')]
    private ?\DateTime $end = null;

    #[ORM\Column(type: 'datetime')]
    #[GQL\Field(type: 'DateTimeScalar!')]
    #[GQL\Description('The final date and time users may (de)register for this activity.')]
    #[Assert\NotBlank]
    #[Assert\Expression('value <= this.getStart()', message: 'Aanmelddeadline kan niet na de start van de activiteit vallen.')]
    private ?\DateTime $deadline = null;

    /** @var File */
    #[Vich\UploadableField(
        mapping: 'activities',
        fileNameProperty: 'image.name',
        size: 'image.size',
        mimeType: 'image.mimeType',
        originalName: 'image.originalName',
        dimensions: 'image.dimensions')]
    private $imageFile;

    #[ORM\Embedded(class: "Vich\UploaderBundle\Entity\File")]
    private EmbeddedFile $image;

    #[ORM\Column(type: 'datetime')]
    private \DateTime $imageUpdatedAt;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[GQL\Field(type: 'Int')]
    #[GQL\Description('The maximum number of users that can be registered for this activity.')]
    #[Assert\PositiveOrZero]
    private ?int $capacity = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[GQL\Field(type: 'Int')]
    #[GQL\Description('A stored number of users that were present at this activity.')]
    #[Assert\PositiveOrZero]
    private ?int $present;

    #[ORM\Column(type: 'datetime', nullable: true, options: ['default' => '1970-01-01 00:00:00'])]
    #[GQL\Field(type: 'DateTimeScalar')]
    #[GQL\Description('The time after which the activity will be publicized.')]
    private ?\DateTime $visibleAfter;

    public function __construct()
    {
        $this->registrations = new ArrayCollection();
        $this->options = new ArrayCollection();
        $this->image = new EmbeddedFile();
        $this->visibleAfter = new \DateTime();
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getArchived(): bool
    {
        return $this->archived;
    }

    public function setArchived(bool $archived): self
    {
        $this->archived = $archived;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /** @return Collection<int, PriceOption> */
    public function getOptions(): Collection
    {
        return $this->options;
    }

    public function addOption(PriceOption $option): self
    {
        if (!$this->options->contains($option)) {
            $this->options[] = $option;
            $option->setActivity($this);
        }

        return $this;
    }

    public function removeOption(PriceOption $option): self
    {
        if ($this->options->contains($option)) {
            $this->options->removeElement($option);
            // set the owning side to null (unless already changed)
            if ($option->getActivity() === $this) {
                $option->setActivity(null);
            }
        }

        return $this;
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
            $registration->setActivity($this);
        }

        return $this;
    }

    public function removeRegistration(Registration $registration): self
    {
        if ($this->registrations->contains($registration)) {
            $this->registrations->removeElement($registration);
            // set the owning side to null (unless already changed)
            if ($registration->getActivity() === $this) {
                $registration->setActivity(null);
            }
        }

        return $this;
    }

    /** @return Collection<int, Registration> */
    public function getCurrentRegistrations(): Collection
    {
        $current = $this->getRegistrations()->filter(fn (Registration $reg) => !$reg->isReserve() && !$reg->isDeleted());

        // Don't retain original indices
        return new ArrayCollection($current->getValues());
    }

    public function addCurrentRegistration(Registration $registration): self
    {
        return $this->addRegistration($registration);
    }

    public function removeCurrentRegistration(Registration $registration): self
    {
        return $this->removeRegistration($registration);
    }

    /** @return Collection<int, Registration> */
    public function getDeregistrations(): Collection
    {
        $deregs = $this->getRegistrations()->filter(fn (Registration $reg) => !$reg->isReserve() && $reg->isDeleted());

        // Don't retain original indices
        return new ArrayCollection($deregs->getValues());
    }

    public function addDeregistration(Registration $registration): self
    {
        return $this->addRegistration($registration);
    }

    public function removeDeregistration(Registration $registration): self
    {
        return $this->removeRegistration($registration);
    }

    /** @return Collection<int, Registration> */
    public function getReserveRegistrations(): Collection
    {
        $reserve = $this->getRegistrations()->filter(fn (Registration $reg) => $reg->isReserve() && !$reg->isDeleted());

        // Don't retain original indices
        $array = $reserve->getValues();
        \usort($array, fn (Registration $a, Registration $b) => $a->getReservePosition() <=> $b->getReservePosition());

        return new ArrayCollection($array);
    }

    public function addReserveRegistration(Registration $registration): self
    {
        return $this->addRegistration($registration);
    }

    public function removeReserveRegistration(Registration $registration): self
    {
        return $this->removeRegistration($registration);
    }

    public function getAuthor(): ?Group
    {
        return $this->author;
    }

    public function setAuthor(?Group $author): self
    {
        $this->author = $author;

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

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(string $color): self
    {
        $this->color = $color;

        return $this;
    }

    public function getStart(): ?\DateTime
    {
        return $this->start;
    }

    public function setStart(\DateTime $start): self
    {
        $this->start = $start;

        return $this;
    }

    public function getEnd(): ?\DateTime
    {
        return $this->end;
    }

    public function setEnd(\DateTime $end): self
    {
        $this->end = $end;

        return $this;
    }

    public function getDeadline(): ?\DateTime
    {
        return $this->deadline;
    }

    public function setDeadline(\DateTime $deadline): self
    {
        $this->deadline = $deadline;

        return $this;
    }

    public function getLocation(): ?Location
    {
        return $this->location;
    }

    public function setLocation(Location $location): self
    {
        $this->location = $location;

        return $this;
    }

    /** @param File|UploadedFile $imageFile */
    public function setImageFile(File $imageFile = null): self
    {
        $this->imageFile = $imageFile;

        if (null !== $imageFile) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->imageUpdatedAt = new \DateTime();
        }

        return $this;
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function setImage(EmbeddedFile $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getImage(): ?EmbeddedFile
    {
        return $this->image;
    }

    public function getImageUpdatedAt(): ?\DateTime
    {
        return $this->imageUpdatedAt;
    }

    public function setImageUpdatedAt(\DateTime $imageUpdatedAt): self
    {
        $this->imageUpdatedAt = $imageUpdatedAt;

        return $this;
    }

    public function hasCapacity(): bool
    {
        return !\is_null($this->capacity);
    }

    public function getCapacity(): ?int
    {
        return $this->capacity;
    }

    public function setCapacity(?int $capacity): self
    {
        $this->capacity = $capacity;

        return $this;
    }

    /**
     * Returns whether the activity is at/over capacity
     * If so, new registrations should be placed in the reserve list.
     */
    public function atCapacity(): bool
    {
        return $this->hasCapacity() && ($this->getCurrentRegistrations()->count() >= $this->getCapacity() || $this->getReserveRegistrations()->count() > 0);
    }

    public function getPresent(): ?int
    {
        return $this->present;
    }

    public function setPresent(?int $present): self
    {
        $this->present = $present;

        return $this;
    }

    /**
     * Get the time after which the activity will be visible for users.
     *
     * @return \DateTime
     */
    public function getVisibleAfter(): ?\DateTime
    {
        return $this->visibleAfter;
    }

    /**
     * Set the time after which the activity will be visible for users.
     */
    public function setVisibleAfter(?\DateTime $visibleAfter): self
    {
        $this->visibleAfter = $visibleAfter;

        return $this;
    }

    /**
     * Is the activity currently visible, given a number of applicable groups.
     *
     * @param Group[] $groups
     */
    public function isVisible(array $groups = []): bool
    {
        $in_groups = null === $this->getTarget() || in_array($this->getTarget(), $groups, true);

        return
            $this->getEnd() > new \DateTime() &&
            $in_groups &&
            null !== $this->getVisibleAfter() &&
            $this->getVisibleAfter() < new \DateTime();
    }

    /**
     * Is the activity currently visible by a user.
     */
    public function isVisibleBy(?LocalAccount $user = null): bool
    {
        // gather the currently applicable groups
        $groups = [];
        if (null !== $user) {
            $groups = $user->getRelations()->toArray();
        }

        return $this->isVisible($groups);
    }
}
