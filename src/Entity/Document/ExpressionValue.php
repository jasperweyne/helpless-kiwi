<?php

namespace App\Entity\Document;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Document\ExpressionValueRepository")
 */
class ExpressionValue implements ValueInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Document\Expression", fetch="EAGER")
     * @ORM\JoinColumn()
     */
    private $expression;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Document\Document", inversedBy="fieldValues", fetch="EAGER")
     * @ORM\JoinColumn(nullable=false)
     */
    private $document;

    /**
     * @ORM\Column(type="text")
     */
    private $value;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $builtin;

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

    public function getField(): ?FieldInterface
    {
        return $this->expression;
    }

    public function getExpression(): ?Expression
    {
        return $this->expression;
    }

    public function setExpression(?Expression $expression): self
    {
        $this->expression = $expression;

        return $this;
    }

    public function getDocument(): ?Document
    {
        return $this->document;
    }

    public function setDocument(?Document $document): self
    {
        $this->document = $document;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->getExpression()->evalValue($this->getDocument());
    }

    public function getBuiltin(): ?string
    {
        //Dont know this and stuff. 
        return $this->builtin;
    }

    public function setBuiltin(?string $builtin): self
    {
        $this->builtin = $builtin;

        return $this;
    }

    public function __toString()
    {
        return $this->getValue();
    }
}
