<?php

namespace App\Entity\Group;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Taxonomy
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100, name="title")
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Group\Taxonomy", inversedBy="children")
     * @ORM\JoinColumn(name="parent", referencedColumnName="id")
     */
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Group\Taxonomy", mappedBy="parent")
     */
    private $children;

    /**
     * @ORM\Column(type="boolean")
     */
    private $category;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Group\Relation", mappedBy="taxonomy", orphanRemoval=true)
     */
    private $relations;

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
     *
     * @param string $name
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return Collection|Taxonomy[]
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function addChild(Taxonomy $child): self
    {
        if (!$this->children->contains($child)) {
            $this->children[] = $child;
            $child->setParent($this);
        }

        return $this;
    }

    public function removeChild(Taxonomy $child): self
    {
        if ($this->children->contains($child)) {
            $this->children->removeElement($child);
            // set the owning side to null (unless already changed)
            if ($child->getParent() === $this) {
                $child->setParent(null);
            }
        }

        return $this;
    }

    public function isCategory(): bool
    {
        return $this->category ?? false;
    }

    public function getCategory(): ?bool
    {
        return $this->category;
    }

    public function setCategory(bool $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function __construct()
    {
        $this->children = new ArrayCollection();
        $this->category = false;
        $this->relations = new ArrayCollection();
    }

    /**
     * @return Collection|Relation[]
     */
    public function getRelations(): Collection
    {
        return $this->relations;
    }

    public function addRelation(Relation $relation): self
    {
        if (!$this->relations->contains($relation)) {
            $this->relations[] = $relation;
            $relation->setTaxonomy($this);
        }

        return $this;
    }

    public function removeRelation(Relation $relation): self
    {
        if ($this->relations->contains($relation)) {
            $this->relations->removeElement($relation);
            // set the owning side to null (unless already changed)
            if ($relation->getTaxonomy() === $this) {
                $relation->setTaxonomy(null);
            }
        }

        return $this;
    }
}
