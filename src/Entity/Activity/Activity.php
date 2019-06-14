<?php

namespace App\Entity\Activity;

use App\Entity\Group\Group;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Activity extends Group
{
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
}
