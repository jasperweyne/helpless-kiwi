<?php

namespace App\Entity\Group;

use App\Entity\Security\LocalAccount;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Overblog\GraphQLBundle\Annotation as GQL;

/**
 * @ORM\Entity
 * @GQL\Type
 * @GQL\Description("A representation of membership status for a user in a group.")
 */
class Relation
{
    /**
     * @var string | null
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     */
    private $id;

    /**
     * @var string | null
     * @ORM\Column(type="string", length=255, nullable=true)
     * @GQL\Field(type="String")
     * @GQL\Description("A textual description of membership status of a user in a group.")
     */
    private $description;

    /**
     * @var Group | null
     * @ORM\ManyToOne(targetEntity="App\Entity\Group\Group", inversedBy="relations")
     * @ORM\JoinColumn(nullable=false)
     * @GQL\Field(type="Group!")
     * @GQL\Description("The group the user is a member of.")
     */
    private $group;

    /**
     * @var LocalAccount | null
     * @ORM\ManyToOne(targetEntity="App\Entity\Security\LocalAccount", inversedBy="relations")
     * @ORM\JoinColumn(name="person_id", referencedColumnName="id")
     * @GQL\Field(type="LocalAccount")
     * @GQL\Description("The user who is a member of a group.")
     * @GQL\Access("hasRole('ROLE_ADMIN')")
     */
    private $person;

    /**
     * @var Relation | null
     * @ORM\ManyToOne(targetEntity="App\Entity\Group\Relation", inversedBy="children")
     * @GQL\Field(type="Relation")
     * @GQL\Description("The parent relation object, in case of multiple overlapping relations.")
     */
    private $parent;

    /**
     * @var Collection<int, Relation>
     * @ORM\OneToMany(targetEntity="App\Entity\Group\Relation", mappedBy="parent")
     * @GQL\Field(type="[Relation]")
     * @GQL\Description("The children relation objects, in case of multiple overlapping relations.")
     */
    private $children;

    public function __construct()
    {
        $this->children = new ArrayCollection();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getGroup(): ?Group
    {
        return $this->group;
    }

    public function setGroup(?Group $group): self
    {
        $this->group = $group;

        return $this;
    }

    public function getPerson(): ?LocalAccount
    {
        return $this->person;
    }

    public function setPerson(?LocalAccount $person): self
    {
        $this->person = $person;

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
     * @return Collection<int, Relation>|self[]
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function addChild(self $child): self
    {
        if (!$this->children->contains($child)) {
            $this->children[] = $child;
            $child->setParent($this);
        }

        return $this;
    }

    public function removeChild(self $child): self
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

    //TODO, changed the logic a little to appeas phpstan needs a test
    public function getRoot(): self
    {
        if (!is_null($this->getParent())) {
            return $this->getParent()->getRoot();
        }

        return $this;
    }

    //TODO is this the correct returnType, feels ugly but it seems to work.
    //Can't find any code that disproves this

    /** @return Collection<int|string, mixed> */
    public function getChildrenRecursive(): Collection
    {
        $childTaxes = $this->children->map(function ($a) {
            $children = $a->getChildrenRecursive()->toArray();
            $children[] = $a;

            return $children;
        })->toArray();

        $taxonomies = array_merge([], ...$childTaxes);

        return new ArrayCollection($taxonomies);
    }

    //TODO  same as with getChildrenRecursive

    /** @return Collection<int|string, mixed> */
    public function getAllRelations(): Collection
    {
        $tree = $this->getRoot()->getChildrenRecursive();
        $tree->add($this);

        return $tree;
    }
}
