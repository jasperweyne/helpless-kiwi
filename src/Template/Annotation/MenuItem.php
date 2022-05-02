<?php

namespace App\Template\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Target("METHOD")
 */
class MenuItem
{
    /**
     * @Required
     *
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $menu;

    /**
     * @var string
     */
    public $role;

    /**
     * @var string
     */
    public $class;

    /**
     * @var string
     */
    public $activeCriteria;

    /**
     * @var int
     */
    public $order;

    /**
     * @var string
     */
    private $path;

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getMenu(): ?string
    {
        return $this->menu;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function getClass(): ?string
    {
        return $this->class;
    }

    public function getActiveCriteria(): ?string
    {
        return $this->activeCriteria;
    }

    public function getOrder(): ?int
    {
        return $this->order;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(?string $path): self
    {
        $this->path = $path;

        return $this;
    }
}
