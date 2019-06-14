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
     * @ORM\ManyToOne(targetEntity="App\Entity\Group\Group", inversedBy="children")
     * @ORM\JoinColumn(name="parent", referencedColumnName="id")
     */
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Group\Group", mappedBy="parent")
     */
    private $children;
}
