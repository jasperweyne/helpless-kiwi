<?php

namespace App\Entity\Group;

use App\Entity\Security\LocalAccount;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Overblog\GraphQLBundle\Annotation as GQL;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: "App\Repository\GroupRepository")]
#[ORM\Table('taxonomy')]
#[GQL\Type]
#[GQL\Description('A group of persons.')]
class Group
{
    /**
     * @var ?string
     */
    #[ORM\Id()]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: 'guid')]
    #[ORM\CustomIdGenerator('doctrine.uuid_generator')]
    private $id;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 100, name: 'title')]
    #[GQL\Field(type: 'String!')]
    #[GQL\Description('The name of the group.')]
    #[Assert\NotBlank]
    private $name;

    /**
     * @var ?string
     */
    #[ORM\Column(type: 'text', nullable: true)]
    #[GQL\Field(type: 'String')]
    #[GQL\Description('A textual description of the the group.')]
    private $description;

    /**
     * @var ?Group
     */
    #[ORM\ManyToOne(targetEntity: "App\Entity\Group\Group", inversedBy: 'children')]
    #[ORM\JoinColumn(name: 'parent', referencedColumnName: 'id')]
    #[GQL\Field(type: 'Group')]
    #[GQL\Description("The parent group of this (sub)group. Note that the members don't need to be a subset of the parent group.")]
    private $parent;

    /**
     * @var Collection<int, Group>
     */
    #[ORM\OneToMany(targetEntity: "App\Entity\Group\Group", mappedBy: 'parent')]
    #[GQL\Field(type: '[Group]')]
    #[GQL\Description("The child (sub)groups of this group. Note that their members don't need to be a subset of this group.")]
    protected $children;

    /**
     * @var bool
     */
    #[ORM\Column(type: 'boolean')]
    #[GQL\Field(type: 'Boolean!')]
    #[GQL\Description('Whether the group can be modified.')]
    private $readonly;

    /**
     * @var ?bool
     */
    #[ORM\Column(type: 'boolean', nullable: true)]
    #[GQL\Field(type: 'Boolean')]
    #[GQL\Description('Whether the group can contain member users.')]
    private $relationable;

    /**
     * @var ?bool
     */
    #[ORM\Column(type: 'boolean', nullable: true)]
    #[GQL\Field(type: 'Boolean')]
    #[GQL\Description('Whether the group can contain children (sub)groups.')]
    private $subgroupable;

    /**
     * @var Collection<int, LocalAccount>
     */
    #[ORM\ManyToMany(targetEntity: LocalAccount::class, mappedBy: 'relations')]
    #[GQL\Field(type: '[LocalAccount]')]
    #[GQL\Description('The member users of this group.')]
    private $relations;

    /**
     * @var bool
     */
    #[ORM\Column(type: 'boolean')]
    #[GQL\Field(type: 'Boolean!')]
    #[GQL\Description('Whether the group is currently active, eg. whether it can organise activities.')]
    private $active;

    /**
     * @var ?bool
     */
    #[ORM\Column(type: 'boolean', nullable: true)]
    #[GQL\Field(type: 'Boolean')]
    #[GQL\Description('Whether the group can be currently used as a target group for activities.')]
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
        // If true, then the group is allowed members.
        // Otherwise no member, but only subgroups are allowed.
        return $this->relationable;
    }

    public function setRelationable(bool $relationable): self
    {
        $this->relationable = $relationable;

        return $this;
    }

    public function getSubgroupable(): ?bool
    {
        // If true, then the group is allowed members.
        // Otherwise no member, but only subgroups are allowed.
        return $this->subgroupable;
    }

    public function setSubgroupable(bool $subgroupable): self
    {
        $this->subgroupable = $subgroupable;

        return $this;
    }

    /**
     * @return Collection<int, LocalAccount>
     */
    public function getRelations(): Collection
    {
        return $this->relations;
    }

    public function addRelation(LocalAccount $relation): self
    {
        if (!$this->relations->contains($relation)) {
            $this->relations->add($relation);
            $relation->addRelation($this);
        }

        return $this;
    }

    public function removeRelation(LocalAccount $relation): self
    {
        if ($this->relations->removeElement($relation)) {
            $relation->removeRelation($this);
        }

        return $this;
    }

    /**
     * Returns a list of all relations related to this group or its parent
     * groups for the provided user. Relations are ordered based on the group
     * hierarchy, from the root parent group down to the current group.
     *
     * @return Collection<int, Group>
     */
    public function getAllRelationFor(LocalAccount $user): Collection
    {
        // if a parent group is present, retrieve those relations first
        $relationList = null !== $this->parent ? $this->parent->getAllRelationFor($user) : new ArrayCollection();

        // add relations to the list (assumption is made of at most one relation per user)
        if ($user->getRelations()->exists(fn ($_, Group $relation) => $relation === $this)) {
            $relationList->add($this);
        }

        return $relationList;
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
