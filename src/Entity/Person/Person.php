<?php

namespace App\Entity\Person;

use App\Entity\Security\Auth;
use App\Entity\Document\Field\FieldValue;
use App\Entity\Document\Document;
use App\Entity\Document\Field\ValueInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PersonRepository")
 */
class Person
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Security\Auth", mappedBy="person")
     */
    private $auth;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $email;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Document\Document")
     */
    private $document;

    //REMOVE THESE CLASSES AFTER UPDATE!!. ONLY.
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Person\PersonValue", mappedBy="person", orphanRemoval=true, cascade={"persist","remove"}, fetch="EAGER")
     */
    private $fieldValues;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Person\PersonScheme")
     */
    private $scheme;


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

    /**
     * Get authentication entity.
     *
     * @return string
     */
    public function getAuth(): ?Auth
    {
        return $this->auth;
    }

    /**
     * Set authentication entity.
     *
     * @param Auth $auth
     */
    public function setAuth(?Auth $auth): self
    {
        $this->auth = $auth;

        return $this;
    }

    /**
     * Get email address.
     *
     * @return string
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * Set email address.
     *
     * @param string $email
     */
    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getValue($key): ?ValueInterface
    {   
        return $this->document->getKeyValue($key);
    }

    /**
     * @return Collection|Value[]
     */
    public function getKeyValues(): Collection
    {
        return $this->document->getKeyValues();
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

    public function getDocument(): ?Document
    {
        return $this->document;
    }

    public function setDocument(?Document $document): self
    {
        $this->document = $document;

        return $this;
    }

    public function getName(): ?string
    {
        return "name";
    }

    public function getShortname(): ?string
    {
        return "shortname";
    }

    public function getCanonical(): ?string
    {
        return "canonical";
    }

    public function __toString()
    {
        return $this->getCanonical();
    }

    /**
     * @return Collection|PersonValue[]
     */
    public function getOldFieldValues(): Collection
    {
        return $this->fieldValues;
    }

    public function getOldScheme(): ?PersonScheme
    {
        return $this->scheme;
    }

    /**
     * @return Collection|AccesGroup[]
     */
    public function userAccesGroups(Document $document): Collection 
    {
        $accesGroups = clone $document->getScheme()->getAccesGroups();
        $accesGroups->filter( function($x) use ($document) { $x->inGroup($this,$document); } );

        return $accesGroups;
    }
}
