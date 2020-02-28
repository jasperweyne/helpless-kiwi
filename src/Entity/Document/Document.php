<?php

namespace App\Entity\Document;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Document\DocumentRepository")
 */
class Document
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     */
    private $id;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Document\FieldValue", mappedBy="document", orphanRemoval=true, fetch="EAGER")
     */
    private $fieldValues;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Document\ExpressionValue", mappedBy="document", orphanRemoval=true, fetch="EAGER")
     */
    private $expressionValues;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Document\Scheme")
     */
    private $scheme;

    public function __construct()
    {
        $this->fieldValues = new ArrayCollection();
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

    public function getScheme(): ?Scheme
    {
        return $this->scheme;
    }

    public function setScheme(?Scheme $scheme): self
    {
        $this->scheme = $scheme;

        return $this;
    }

    public function getFieldValue($field): ?FieldValue
    {
        foreach ($this->fieldValues as $value) {
            if ($field instanceof Field) {
                $valueField = $value->getField();
                if (!is_null($valueField) && $valueField->getId() == $field->getId()) {
                    return $value;
                }
            } else {
                if ($value->getBuiltin() == $field) {
                    return $value;
                }
            }
        }

        return null;
    }

    /**
     * @return Collection|PersonValue[]
     */
    public function getFieldValues(): Collection
    {
        return $this->fieldValues;
    }

    public function addFieldValue(FieldValue $value): self
    {
        if (!$this->fieldValues->contains($value)) {
            $this->fieldValues[] = $value;
            $value->setDocument($this);
        }

        return $this;
    }

    public function removeFieldValue(FieldValue $value): self
    {
        if ($this->fieldValues->contains($value)) {
            $this->fieldValues->removeElement($value);
            // set the owning side to null (unless already changed)
            if ($value->getDocument() === $this) {
                $value->setDocument(null);
            }
        }

        return $this;
    }

    public function getExpressionValue($expression): ?ExpressionValue
    {
        foreach ($this->expressionValues as $value) {
            if ($expression instanceof Expression) {
                $expressionField = $value->getField();
                if (!is_null($expressionField) && $expressionField->getId() == $expression->getId()) {
                    return $value;
                }
            } else {
                /*
                if ($value->getBuiltin() == $expression) {
                    return $value;
                }
                */
            
            }
        }

        return null;
    }
    
    /**
     * @return Collection|ExpressionValue[]
     */
    public function getExpressionValues(): Collection
    {
        return $this->expressionValues;
    }

    public function addExpressionValue(ExpressionValue $value): self
    {
        if (!$this->expressionValues->contains($value)) {
            $this->expressionValues[] = $value;
            $value->setDocument($this);
        }

        return $this;
    }

    public function removeExpressionValue(ExpressionValue $value): self
    {
        if ($this->expressionValues->contains($value)) {
            $this->expressionValues->removeElement($value);
            // set the owning side to null (unless already changed)
            if ($value->getDocument() === $this) {
                $value->setDocument(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|PersonValue[]
     */
    public function getKeyValues(): Collection
    {
        $keyVals = new ArrayCollection();
        if ($this->getScheme()) {
            foreach ($this->getScheme()->getFields() as $field) {
                $keyVals[] = [
                    'key' => $field,
                    'value' => $this->getFieldValue($field),
                ];
            }
            foreach ($this->getScheme()->getExpressions() as $expression) {
                $keyVals[] = [
                    'key' => $expression,
                    'value' => $this->getExpressionValue($expression),
                ];
            }
        } else {
            foreach ($this->getFieldValues() as $value) {
                $keyVals[] = [
                    'key' => $value->getBuiltin() ?? $value->getField(),
                    'value' => $value,
                ];
            }
        }

        return $keyVals;
    }


    public function getKeyValue($key): ?ValueInterface
    {
        foreach ($this->expressionValues as $value) {
            if ($key instanceof Expression) {
                $expression = $value->getExpression();
                if (!is_null($expression) && $expression->getId() == $key->getId()) {
                    return $value;
                }
            } else {
                /*
                if ($value->getBuiltin() == $expression) {
                    return $value;
                }
                */
            }
        }

        foreach ($this->fieldValues as $value) {
            if ($key instanceof Field) {
                $field = $value->getField();
                if (!is_null($field) && $field->getId() == $key->getId()) {
                    return $value;
                }
            } else {
                /*
                if ($value->getBuiltin() == $expression) {
                    return $value;
                }
                */
            }
        }


        return null;
    }

    


    
}
