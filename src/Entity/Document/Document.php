<?php

namespace App\Entity\Document;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Document/DocumentRepository")
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
     * @ORM\OneToMany(targetEntity="App\Entity\Document\Value", mappedBy="person", orphanRemoval=true, fetch="EAGER")
     */
    private $fieldValues;

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

    public function getValue($field): ?Value
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

    public function addFieldValue(Value $fieldValue): self
    {
        if (!$this->fieldValues->contains($fieldValue)) {
            $this->fieldValues[] = $fieldValue;
            $fieldValue->setDocument($this);
        }

        return $this;
    }

    public function removeFieldValue(Value $fieldValue): self
    {
        if ($this->fieldValues->contains($fieldValue)) {
            $this->fieldValues->removeElement($fieldValue);
            // set the owning side to null (unless already changed)
            if ($fieldValue->getDocument() === $this) {
                $fieldValue->setDocument(null);
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
                    'value' => $this->getValue($field),
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

    public function getShortnameExpr(): ?string
    {
        return $this->shortname_expr;
    }

    public function setShortnameExpr(?string $shortname_expr): self
    {
        $this->shortname_expr = $shortname_expr;

        return $this;
    }

    public function getNameExpr(): ?string
    {
        return $this->name_expr;
    }

    public function setNameExpr(?string $name_expr): self
    {
        $this->name_expr = $name_expr;

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

    
}
