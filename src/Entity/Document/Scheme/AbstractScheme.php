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
 * @ORM\Entity
 * @ORM\InheritanceType("SINGLE_TABLE")
 * 
 * */
class AbstractScheme
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Document\Field\Expression", mappedBy="scheme")
     */
    private $expressions;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Document\Field\Field", mappedBy="scheme")
     */
    private $fields;

    public function __construct()
    {
        $this->fields = new ArrayCollection();
        $this->expressions = new ArrayCollection();
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection|Field[]
     */
    public function getFields(): Collection
    {
        return $this->fields;
    }

    public function getField($name): ?Field 
    {
        foreach ($this->fields as $field) {
            if ($field->getName() == $name ) {
               return $field;
            } 
        }
        return null;
    }

    public function addField(Field $field): self
    {
        if ($this->fields){
            if (!$this->fields->contains($field)) {
                $this->fields[] = $field;
                $field->setScheme($this);
            }
        } else {
            $this->fields[] = $field;
            $field->setScheme($this); 
        }
        

        return $this;
    }

    public function removeField(Field $field): self
    {
        if ($this->fields->contains($field)) {
            $this->fields->removeElement($field);
            // set the owning side to null (unless already changed)
            if ($field->getScheme() === $this) {
                $field->setScheme(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Expression[]
     */
    public function getExpressions(): Collection
    {
        return $this->expressions;
    }

    public function getExpression($name): ?Expression {
        foreach ($this->expressions as $expression) {
            if ($expression->getName() == $name ) {
               return $expression;
            } 
        }
        return null;
    }

    public function addExpression(Expression $expression): self
    {
        if ($this->expressions){
            if (!$this->expressions->contains($expression)) {
                $this->expressions[] = $expression;
                $expression->setScheme($this);
            }
        } else {
            $this->expressions[] = $expression;
            $expression->setScheme($this);
        }
        

        return $this;
    }

    public function removeExpression(Expression $expression): self
    {
        if ($this->expressions->contains($expression)) {
            $this->expressions->removeElement($expression);
            // set the owning side to null (unless already changed)
            if ($expression->getScheme() === $this) {
                $expression->setScheme(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|FieldInterface[]
     */
    public function getKeys(): Collection
    {   
        $keys = new ArrayCollection();
        foreach ($this->fields as $key) {
            $keys[] = $key;
        }
        
        foreach ($this->expressions as $key) {
            $keys[] = $key;
        }

        return $keys;
    }

}
