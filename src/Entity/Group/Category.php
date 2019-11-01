<?php

namespace App\Entity\Group;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Category extends Taxonomy
{
    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $hasInstances;

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
     * @return Collection|Group[]
     */
    public function getInstances(): Collection
    {
        return $this->children->filter(function ($x) { return $x instanceof Group; });
    }

    public function addInstance(Group $group): self
    {
        if (!$this->children->contains($group)) {
            $this->children[] = $group;
            $group->setParent($this);
        }

        return $this;
    }

    public function removeInstance(Group $group): self
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
}
