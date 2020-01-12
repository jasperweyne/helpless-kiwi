<?php

namespace App\Entity\Group;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\InheritanceType("SINGLE_TABLE")
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
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Group\Taxonomy", inversedBy="children")
     * @ORM\JoinColumn(name="parent", referencedColumnName="id")
     */
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Group\Taxonomy", mappedBy="parent")
     */
    protected $children;

    /**
     * @ORM\Column(type="boolean")
     */
    private $readonly;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $relationable;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $subgroupable;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Group\Relation", mappedBy="taxonomy", orphanRemoval=true)
     */
    private $relations;

    public function __construct()
    {
        $this->relations = new ArrayCollection();
        $this->children = new ArrayCollection();
        $this->readonly = false;
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

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    public function getReadonly(): ?bool
    {
        return $this->readonly;
    }

    public function setReadonly(bool $readonly): self
    {
        $this->readonly = $readonly;

        return $this;
    }

    public function getRelationable(): ?bool
    {
        //If true, then the group is allowed members.
        //Otherwise no member, but only subgroups are allowed.
        return $this->relationable;
    }

    public function setRelationable(bool $relationable): self
    {
        $this->relationable = $relationable;

        return $this;
    }

    public function getSubgroupable(): ?bool
    {
        //If true, then the group is allowed members.
        //Otherwise no member, but only subgroups are allowed.
        return $this->subgroupable;
    }

    public function setSubgroupable(bool $subgroupable): self
    {
        $this->subgroupable = $subgroupable;

        return $this;
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

    /**
     * @return Collection|Taxonomy[]
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function addChild(Taxonomy $taxonomy): self
    {
        if (!$this->children->contains($taxonomy)) {
            $this->children[] = $taxonomy;
            $taxonomy->setParent($this);
        }

        return $this;
    }

    public function removeChild(Taxonomy $taxonomy): self
    {
        if ($this->children->contains($taxonomy)) {
            $this->children->removeElement($taxonomy);
            // set the owning side to null (unless already changed)
            if ($taxonomy->getParent() === $this) {
                $taxonomy->setParent(null);
            }
        }

        return $this;
    }
}
