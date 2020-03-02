<?php

namespace App\Entity\Document;

use App\Entity\Person\Person;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use App\Entity\Document\Scheme\Scheme;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Document\AccesGroupRepository")
 */
class AccesGroup
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Document\Scheme\Scheme")
     */
    private $scheme;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;


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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

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

    public function inGroup(Person $person, Document $document): bool
    {
        //This function defines if a user is in this usergroups. (Based on the person and the document accesed.)
        switch($this->name){
            case 'Admin':
                return in_array ('ROLE_ADMIN',$person->getAuth()->getRoles());
            case 'Owner':
                return ($person->getDocument()->getId() == $document->getId());
            default:
                return false;
        }

        //To be sure;
        return false;
    }
}