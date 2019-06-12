<?php

namespace App\Entity\Log;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\InheritanceType("SINGLE_TABLE")
 */
class BaseEvent
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="guid")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Security\Auth")
     * @ORM\JoinColumn(name="auth", referencedColumnName="person")
     */
    private $auth;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Reference")
     * @ORM\JoinColumn(name="user", referencedColumnName="id")
     */
    private $user;

    /**
     * @ORM\Column(type="datetime")
     */
    private $time;

    /**
     * @ORM\Column(type="array")
     */
    private $meta;
}
