<?php

namespace App\Entity\Activity;

use App\Entity\Group\Group;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class PriceOption extends Group
{
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Activity\Activity", inversedBy="priceOptions")
     * @ORM\JoinColumn(name="activity", referencedColumnName="id")
     */
    private $activity;

    /**
     * @ORM\Column(type="datetime")
     */
    private $deadline;

    /**
     * @ORM\Column(type="integer")
     */
    private $price;

    /**
     * @ORM\Column(type="json")
     */
    private $details;

    /**
     * @ORM\Column(type="string")
     */
    private $confirmationMsg;
}
