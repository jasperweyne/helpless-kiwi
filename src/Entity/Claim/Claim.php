<?php

namespace App\Entity\Claim;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Claim
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
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
     * @var string
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=false)
     */
    private $createdAt;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Security\Auth")
     * @ORM\JoinColumn(name="reviewed_by", referencedColumnName="person")
     */
    private $reviewedBy;
}
