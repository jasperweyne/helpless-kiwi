<?php

namespace App\Entity\Group;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Overblog\GraphQLBundle\Annotation as GQL;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\GroupRepository")
 * @ORM\Table("taxonomy")
 * @GQL\Type
 * @GQL\Description("A group of persons.")
 */
class Group
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     *
     * @var ?string
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100, name="title")
     * @GQL\Field(type="String!")
     * @GQL\Description("The name of the group.")
     * @Assert\NotBlank
     *
     * @var string
     */
    private $name;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @GQL\Field(type="String")
     * @GQL\Description("A textual description of the the group.")
     *
     * @var ?string
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Group\Group", inversedBy="children")
     * @ORM\JoinColumn(name="parent", referencedColumnName="id")
     * @GQL\Field(type="Group")
     * @GQL\Description("The parent group of this (sub)group. Note that the members don't need to be a subset of the parent group.")
     *
     * @var ?Group
     */
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Group\Group", mappedBy="parent")
     * @GQL\Field(type="[Group]")
     * @GQL\Description("The child (sub)groups of this group. Note that their members don't need to be a subset of this group.")
     *
     * @var Collection<int, Group>
     */
    protected $children;

    /**
     * @ORM\Column(type="boolean")
     * @GQL\Field(type="Boolean!")
     * @GQL\Description("Whether the group can be modified.")
     *
     * @var bool
     */
    private $readonly;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @GQL\Field(type="Boolean")
     * @GQL\Description("Whether the group can contain member users.")
     *
     * @var ?bool
     */
    private $relationable;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @GQL\Field(type="Boolean")
     * @GQL\Description("Whether the group can contain children (sub)groups.")
     *
     * @var ?bool
     */
    private $subgroupable;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Group\Relation", mappedBy="group", orphanRemoval=true)
     * @GQL\Field(type="[Relation]")
     * @GQL\Description("The member users of this group.")
     *
     * @var Collection<int, Relation>
     */
    private $relations;

    /**
     * @ORM\Column(type="boolean")
     * @GQL\Field(type="Boolean!")
     * @GQL\Description("Whether the group is currently active, eg. whether it can organise activities.")
     *
     * @var bool
     */
    private $active;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @GQL\Field(type="Boolean")
     * @GQL\Description("Whether the group can be currently used as a target group for activities.")
     *
     * @var ?bool
     */
    private $register;

    public function __construct()
    {
        $this->relations = new ArrayCollection();
        $this->children = new ArrayCollection();
        $this->readonly = false;
        $this->active = false;
    }

    /**
     * Get id.
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
     * Get description.
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Set description.
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
     * @return Collection<int, Relation>
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
     * @return Collection<int, Group>
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

    public function getRegister(): ?bool
    {
        return $this->register;
    }

    public function setRegister(bool $register): self
    {
        $this->register = $register;

        return $this;
    }
}
