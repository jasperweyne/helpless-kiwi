<?php

namespace App\Entity\Document;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Document\SchemeRepository")
 */
class Scheme
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Document\Field", mappedBy="scheme")
     */
    private $fields;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Document\Index", mappedBy="scheme")
     */
    private $index;

    public function __construct()
    {
        $this->fields = new ArrayCollection();
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
     *
     * @param string $id
     */
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

    /**
     * @return Collection|PersonField[]
     */
    public function getFields(): Collection
    {
        return $this->fields;
    }

    public function getField($name): ?Field {
        foreach ($this->fields as $field) {
            if ($field->getName() == $name ) {
               return $field;
            } 
        }
        return null;
    }

    public function addField(Field $field): self
    {
        if (!$this->fields->contains($field)) {
            $this->fields[] = $field;
            $field->setScheme($this);
        }

        return $this;
    }

    public function removeField(Field $field): self
    {
        if ($this->fields->contains($field)) {
            $this->fields->removeElement($field);
            // set the owning side to null (unless already changed)
            if ($field->getScheme() === $this) {
                $field->setScheme(null);
            }
        }

        return $this;
    }
}
