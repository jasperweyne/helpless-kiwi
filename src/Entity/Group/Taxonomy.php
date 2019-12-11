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
     * @ORM\ManyToOne(targetEntity="App\Entity\Group\Taxonomy", inversedBy="children")
     * @ORM\JoinColumn(name="parent", referencedColumnName="id")
     */
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Group\Taxonomy", mappedBy="parent")
     */
    protected $children;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $hasChildren;

    /**
     * @ORM\Column(type="boolean")
     */
    private $readonly;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isGroup;

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



    public function getNoChildren(): ?bool
    {
        return null === $this->hasChildren ? null : !$this->hasChildren;
    }

    public function getNode(): ?bool
    {
        return $this->hasChildren;
    }

    public function setHasChildren(bool $hasChildren): self
    {
        $this->hasChildren = $hasChildren;

        return $this;
    }

    public function getNoInstances(): ?bool
    {
        return null === $this->hasInstances ? null : !$this->hasInstances;
    }

    public function getHasInstances(): ?bool
    {
        return $this->hasInstances;
    }

    public function setHasInstances(bool $hasInstances): self
    {
        $this->hasInstances = $hasInstances;

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
    public function getGroups(): Collection
    {
        return $this->children->filter(function ($x) { return $x instanceof Group; });
    }

    /**
     * @return Collection|Taxonomy[]
     */
    public function getSubCategories(): Collection
    {
        return $this->children->filter(function ($x) { return $x instanceof Category; });
    }


    public function addTaxonomy(Taxonomy $taxonomy): self
    {
        if (!$this->children->contains($taxonomy)) {
            $this->children[] = $taxonomy;
            $taxonomy->setParent($this);
        }

        return $this;
    }

    public function removeTaxonomy(Taxonomy $taxonomy): self
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
