<?php

namespace App\Entity\Mail;

use App\Entity\Group\Taxonomy;
use App\Entity\Security\Auth;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Mail
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Security\Auth")
     * @ORM\JoinColumn(name="auth", referencedColumnName="person")
     */
    private $auth;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Group\Taxonomy")
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

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getAuth(): ?Auth
    {
        return $this->auth;
    }

    public function setAuth(?Auth $auth): self
    {
        $this->auth = $auth;

        return $this;
    }

    public function getTarget(): ?Taxonomy
    {
        return $this->target;
    }

    public function setTarget(?Taxonomy $target): self
    {
        $this->target = $target;

        return $this;
    }
}
