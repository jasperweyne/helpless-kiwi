<?php

namespace App\Entity\Activity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Activity
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="guid")
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     */
    private $name;

    /**
     * @ORM\Column(type="string")
     */
    private $description;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Location")
     * @ORM\JoinColumn(name="location", referencedColumnName="id")
     */
    private $location;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Reference")
     * @ORM\JoinColumn(name="primairy_author", referencedColumnName="id")
     */
    private $primairyAuthor;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Activity\PriceOption", mappedBy="activity")
     */
    private $priceOptions;

    /**
     * @ORM\Column(type="datetime")
     */
    private $start;

    /**
     * @ORM\Column(type="datetime")
     */
    private $end;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;
}
