<?php

namespace App\Entity\Group;

use App\Entity\Person\Person;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Relation
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Group\Group", inversedBy="relations")
     * @ORM\JoinColumn(nullable=false)
     */
    private $group;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Person\Person")
     */
    private $person;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Group\Relation", inversedBy="children")
     */
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Group\Relation", mappedBy="parent")
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

    public function getPerson(): ?Person
    {
        return $this->person;
    }

    public function setPerson(?Person $person): self
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
