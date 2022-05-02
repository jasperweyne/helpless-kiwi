<?php

namespace App\Entity\Log;

use App\Entity\Security\LocalAccount;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(
 *   name="log",
 *   indexes={
 *     @ORM\Index(name="search_idx", columns={"object_id", "object_type"}),
 *     @ORM\Index(name="order_idx",  columns={"time"}),
 *     @ORM\Index(name="discr_idx",  columns={"discr"})
 *   }
 * )
 */
class Event
{
    /**
     * @var string | null
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=100)
     */
    private $discr;

    /**
     * @var DateTimeInterface
     * @ORM\Column(type="datetime")
     */
    private $time;

    /**
     * @var string | null
     * @ORM\Column(type="string", nullable=true)
     */
    private $objectId;

    /**
     * @var string | null
     * @ORM\Column(type="string", nullable=true)
     */
    private $objectType;

    /**
     * @var LocalAccount | null
     * @ORM\ManyToOne(targetEntity="App\Entity\Security\LocalAccount")
     * @ORM\JoinColumn(name="person_id", referencedColumnName="id")
     */
    private $person;

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private $meta;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getDiscr(): ?string
    {
        return $this->discr;
    }

    public function setDiscr(string $discr): self
    {
        $this->discr = $discr;

        return $this;
    }

    public function getTime(): ?DateTimeInterface
    {
        return $this->time;
    }

    public function setTime(DateTimeInterface $time): self
    {
        $this->time = $time;

        return $this;
    }

    public function getMeta(): ?string
    {
        return $this->meta;
    }

    public function setMeta(string $meta): self
    {
        $this->meta = $meta;

        return $this;
    }

    public function getPerson(): ?LocalAccount
    {
        return $this->person;
    }

    public function setPerson(?LocalAccount $person): self
    {
        $this->person = $person;

        return $this;
    }

    public function getObjectId(): ?string
    {
        return $this->objectId;
    }

    public function setObjectId(?string $objectId): self
    {
        $this->objectId = $objectId;

        return $this;
    }

    public function getObjectType(): ?string
    {
        return $this->objectType;
    }

    public function setObjectType(?string $objectType): self
    {
        $this->objectType = $objectType;

        return $this;
    }
}
