<?php

namespace App\Template\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class SubmenuItem
{
    public function __construct(
        private string $title,
        private ?string $path = null
    ) {
    }

    public function getTitle(): string
    {
        return $this->title;
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
