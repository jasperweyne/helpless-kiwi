<?php

namespace App\Entity\Group;

use App\Entity\Reference;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Group extends Reference
{
    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Group\Group")
     */
    private $parent;
}
