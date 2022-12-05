<?php

namespace App\Entity\Gallery;

use App\Entity\Activity\Activity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\UploaderBundle\Entity\File as EmbeddedFile;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity]
#[Vich\Uploadable]
class Photo
{
    /**
     * @var ?string
     */
    #[ORM\Id()]
    #[ORM\GeneratedValue(strategy: "UUID")]
    #[ORM\Column(type: "guid")]
    private $id;

    /**
     * @var File
     */
    #[Vich\UploadableField(mapping: "activities", fileNameProperty: "image.name", size: "image.size", mimeType: "image.mimeType", originalName: "image.originalName", dimensions: "image.dimensions")]
    private $imageFile;

    /**
     * @var EmbeddedFile
     */
    #[ORM\Embedded(class: EmbeddedFile::class)]
    private $image;

    /**
     * @var \DateTime
     */
    #[ORM\Column(type: "datetime")]
    private $imageUpdatedAt;

    /**
     * @var Collection<int, Activity>
     */
    #[ORM\OneToMany(mappedBy: 'photo', targetEntity: Activity::class)]
    private $activities;

    public function __construct()
    {
        $this->image = new EmbeddedFile();
        $this->activities = new ArrayCollection();
    }

    /**
     * Get id.
     */
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

    /**
     * @param File|UploadedFile $imageFile
     */
    public function setImageFile(File $imageFile = null): self
    {
        $this->imageFile = $imageFile;

        if (null !== $imageFile) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->imageUpdatedAt = new \DateTimeImmutable();
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

    /**
     * Get imageUpdatedAt.
     *
     * @return \DateTime
     */
    public function getImageUpdatedAt(): ?\DateTime
    {
        return $this->imageUpdatedAt;
    }

    /**
     * Set id.
     */
    public function setImageUpdatedAt(\DateTime $imageUpdatedAt): self
    {
        $this->imageUpdatedAt = $imageUpdatedAt;

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
            $this->activities->add($activity);
            $activity->setPhoto($this);
        }

        return $this;
    }

    public function removeActivity(Activity $activity): self
    {
        if ($this->activities->removeElement($activity)) {
            // set the owning side to null (unless already changed)
            if ($activity->getPhoto() === $this) {
                $activity->setPhoto(null);
            }
        }

        return $this;
    }
}
