<?php

namespace App\Entity\Document\Scheme;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Document\Field\Field;
use App\Entity\Document\Field\Expression;
use App\Entity\Document\Field\FieldInterface;
use App\Entity\Document\Field\ValueInterface;
use App\Entity\Document\Field\FieldValue;

use App\Entity\Document\AccesGroup;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Document\SchemeRepository")
 */
class Scheme extends AbstractScheme
{
   
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Document\Scheme\SchemeDefault", inversedBy="schemes")
     */
    private $schemeDefault;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Document\AccesGroup", mappedBy="scheme")
     */
    private $acces;

    public function __construct()
    {
        $this->fields = new ArrayCollection();
    }

   
    public function getSchemeDefault(): ?SchemeDefault
    {
        return $this->schemeDefault;
    }

    public function setSchemeDefault(SchemeDefault $schemeDefault): self
    {
        $this->schemeDefault = $schemeDefault;

        return $this;
    }

    /**
     * @return Collection|AccesGroup[]
     */
    public function getAccesGroups(): Collection
    {
        return $this->acces;
    }

    public function addAccesGroup(AccesGroup $acces): self
    {
        if (!$this->acces->contains($acces)) {
            $this->acces[] = $acces;
            $acces->setScheme($this);
        }

        return $this;
    }

    public function removeAccesGroup(AccesGroup $acces): self
    {
        if ($this->acces->contains($acces)) {
            $this->acces->removeElement($acces);
            // set the owning side to null (unless already changed)
            if ($acces->getScheme() === $this) {
                $acces->setScheme(null);
            }
        }

        return $this;
    }

}
