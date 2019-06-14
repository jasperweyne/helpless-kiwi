<?php

namespace App\Entity\Mail;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Mail
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
     * @ORM\JoinColumn(name="target", referencedColumnName="id")
     */
    private $target;

    /**
     * @ORM\Column(type="string")
     */
    private $title;

    /**
     * @ORM\Column(type="string")
     */
    private $content;
}
