<?php

namespace App\Template\Attribute;

use Attribute;

#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class SubmenuItem
{
    public function __construct(
        public readonly string $title,
        public ?string $path = null
    ) {
    }
}
