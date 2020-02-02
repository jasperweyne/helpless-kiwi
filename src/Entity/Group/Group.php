<?php

namespace App\Entity\Group;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\GroupRepository")
 * @ORM\Table("taxonomy")
 */
class Group
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100, name="title")
     * @Assert\NotBlank
     */
    private $name;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Group\Group", inversedBy="children")
     * @ORM\JoinColumn(name="parent", referencedColumnName="id")
     */
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Group\Group", mappedBy="parent")
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
     * @ORM\OneToMany(targetEntity="App\Entity\Group\Relation", mappedBy="group", orphanRemoval=true)
     */
    private $relations;

    /**
     * @ORM\Column(type="boolean")
     */
    private $active;

    public function __construct()
    {
        $this->relations = new ArrayCollection();
        $this->children = new ArrayCollection();
        $this->readonly = false;
        $this->active = false;
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
            $relation->setGroup($this);
        }

        return $this;
    }

    public function removeRelation(Relation $relation): self
    {
        if ($this->relations->contains($relation)) {
            $this->relations->removeElement($relation);
            // set the owning side to null (unless already changed)
            if ($relation->getGroup() === $this) {
                $relation->setGroup(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Group[]
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function addChild(Group $group): self
    {
        if (!$this->children->contains($group)) {
            $this->children[] = $group;
            $group->setParent($this);
        }

        return $this;
    }

    public function removeChild(Group $group): self
    {
        if ($this->children->contains($group)) {
            $this->children->removeElement($group);
            // set the owning side to null (unless already changed)
            if ($group->getParent() === $this) {
                $group->setParent(null);
            }
        }

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }
}
