<?php

namespace App\Entity\Document\Scheme;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Document\Field\Field;
use App\Entity\Document\Field\Expression;
use App\Entity\Document\Field\FieldInterface;
use App\Entity\Document\Field\ValueInterface;
use App\Entity\Document\AccesGroup;
use ProxyManager\ProxyGenerator\ValueHolder\MethodGenerator\Constructor;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Document\SchemeDefaultRepository")
 */
class SchemeDefault extends AbstractScheme
{
   
    /**
     * @ORM\Column(type="string", length=255)
     */
    private $schemeType;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Document\Scheme\Scheme", mappedBy="schemeDefault")
     */
    private $schemes;

    public function getSchemeType(): ?string
    {
        return $this->schemeType;
    }

    public function setSchemeType(string $schemeType): self
    {
        $this->schemeType = $schemeType;
        
        return $this;
    }

}
