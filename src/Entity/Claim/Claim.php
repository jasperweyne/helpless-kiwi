<?php

namespace App\Entity\Claim;

use App\Entity\Security\Auth;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Claim
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Security\Auth")
     * @ORM\JoinColumn(name="author", referencedColumnName="person")
     */
    private $author;

    /**
     * @ORM\Column(type="string")
     */
    private $purpose;

    /**
     * @ORM\Column(type="integer")
     */
    private $price;

    /**
     * @ORM\Column(type="string")
     */
    private $description;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Security\Auth")
     * @ORM\JoinColumn(name="reviewed_by", referencedColumnName="person")
     */
    private $reviewedBy;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getPurpose(): ?string
    {
        return $this->purpose;
    }

    public function setPurpose(string $purpose): self
    {
        $this->purpose = $purpose;

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getAuthor(): ?Auth
    {
        return $this->author;
    }

    public function setAuthor(?Auth $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getReviewedBy(): ?Auth
    {
        return $this->reviewedBy;
    }

    public function setReviewedBy(?Auth $reviewedBy): self
    {
        $this->reviewedBy = $reviewedBy;

        return $this;
    }
}
