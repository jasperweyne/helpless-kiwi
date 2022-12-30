<?php

namespace App\Template\Attribute;

use Attribute;

/**
 * @phpstan-import-type MenuItemArray from \App\Template\MenuExtensionInterface
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class MenuItem
{
    /**
     * @param ?SubmenuItem[] $sub
     */
    public function __construct(
        public readonly string $title,
        public readonly ?string $menu = null,
        public readonly ?string $role = null,
        public readonly ?string $class = null,
        public readonly ?string $activeCriteria = null,
        public readonly ?int $order = null,
        public readonly ?array $sub = null,
        public ?string $path = null
    ) {
    }

    /**
     * @return MenuItemArray
     */
    public function toMenuItemArray(): array
    {
        $arr = [
            'title' => $this->title,
            'path' => (string) $this->path,
        ];
        if (null !== $this->role) {
            $arr['role'] = $this->role;
        }
        if (null !== $this->class) {
            $arr['class'] = $this->class;
        }
        if (null !== $this->activeCriteria) {
            $arr['activeCriteria'] = $this->activeCriteria;
        }
        if (null !== $this->order) {
            $arr['order'] = $this->order;
        }
        if (null !== $this->sub) {
            $arr['sub'] = [];
            foreach ($this->sub as $sub) {
                $arr['sub'][] = [
                    'title' => $sub->title,
                    'path' => $sub->path ?? '',
                ];
            }
        }
        return $arr;
    }
}
