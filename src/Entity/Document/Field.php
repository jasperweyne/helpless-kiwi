<?php

namespace App\Entity\Document;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Document\FieldRepository")
 */
class Field implements FieldInterface
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
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $slug;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $valueType;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $userEditOnly;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Document\Scheme", inversedBy="fields")
     */
    private $scheme;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Document\AccesGroup")
     * @ORM\JoinColumn(name="edit_group", referencedColumnName="id", nullable=true)
     */
    private $canEdit;

     /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Document\AccesGroup")
     * @ORM\JoinColumn(name="view_group", referencedColumnName="id", nullable=true)
     */
    private $canView;


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

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getValueType(): ?string
    {
        return $this->valueType;
    }

    public function setValueType(string $valueType): self
    {
        $this->valueType = $valueType;

        return $this;
    }

    public function getUserEditOnly(): ?bool
    {
        return $this->userEditOnly;
    }

    public function setUserEditOnly(bool $userEditOnly): self
    {
        $this->userEditOnly = $userEditOnly;

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

    public function getCanEdit(): ?AccesGroup
    {
        return $this->canEdit;
    }

    public function setCanEdit(?AccesGroup $edit): self
    {
        $this->canEdit = $edit;

        return $this;
    }

    public function getCanView(): ?AccesGroup
    {
        return $this->canView;
    }

    public function setCanView(?AccesGroup $view): self
    {
        $this->canView = $view;

        return $this;
    }
    
    public function __toString()
    {
        return $this->getName();
    }
}
