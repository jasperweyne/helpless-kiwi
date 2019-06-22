<?php

namespace App\Entity\Log;

use App\Entity\Security\Auth;
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
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $discr;

    /**
     * @ORM\Column(type="datetime")
     */
    private $time;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $objectId;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $objectType;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Security\Auth")
     * @ORM\JoinColumn(name="auth", referencedColumnName="person")
     */
    private $auth;

    /**
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

    public function getTime(): ?\DateTimeInterface
    {
        return $this->time;
    }

    public function setTime(\DateTimeInterface $time): self
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

    public function getAuth(): ?Auth
    {
        return $this->auth;
    }

    public function setAuth(?Auth $auth): self
    {
        $this->auth = $auth;

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
