<?php

namespace App\Template\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class MenuItem
{
    public function __construct(
        private string $title,
        private ?string $menu = null,
        private ?string $role = null,
        private ?string $class = null,
        private ?string $activeCriteria = null,
        private ?int $order = null,
        private ?string $path = null
    ) { }

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
