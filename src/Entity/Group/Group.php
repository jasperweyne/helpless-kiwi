<?php

namespace App\Entity\Group;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Group extends Taxonomy
{
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Group\Relation", mappedBy="taxonomy", orphanRemoval=true)
     */
    private $relations;

    public function __construct()
    {
        parent::__construct();

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
