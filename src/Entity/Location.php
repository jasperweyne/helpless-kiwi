<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Location
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="string", length=100)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=false)
     */
    private $created_at;

    /**
     * @ORM\Column(type="string")
     */
    private $address;
}
