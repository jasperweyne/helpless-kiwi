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
 */
class Relation
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     * @GQL\Field(type="String")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @GQL\Field(type="String")
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Group\Group", inversedBy="relations")
     * @ORM\JoinColumn(nullable=false)
     * @GQL\Field(type="Group")
     */
    private $group;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Security\LocalAccount")
     * @ORM\JoinColumn(name="person_id", referencedColumnName="id")
     */
    private $person;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Group\Relation", inversedBy="children")
     * @GQL\Field(type="Relation")
     */
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Group\Relation", mappedBy="parent")
     * @GQL\Field(type="Relation")
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
     * @return Collection|self[]
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

    public function getRoot(): self
    {
        if ($this->parent) {
            return $this->parent->getRoot();
        }

        return $this;
    }

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

    public function getAllRelations(): Collection
    {
        $tree = $this->getRoot()->getChildrenRecursive();
        $tree->add($this);

        return $tree;
    }
}
