<?php

namespace App\Template;

class MenuBuilder
{
    /**
     * @var MenuExtensionInterface[]
     */
    private $extensions;

    /**
     * @var array<string, array{title: string, path: string|array{0: ?string, 1: array{id: ?string}}, role?: string, class?: string, activeCriteria?: string, order?: int}[]>
     */
    private $menuitems;

    /**
     * @param MenuExtensionInterface[] $extensions
     */
    public function __construct($extensions)
    {
        $this->extensions = $extensions;
        $this->menuitems = [];
    }

    /**
     * Returns the extensions loaded by the framework.
     *
     * @return MenuExtensionInterface[]
     */
    public function getExtensions()
    {
        return $this->extensions;
    }

    /**
     * Returns a list of available menu items.
     *
     * @return array{title: string, path: string|array{0: ?string, 1: array{id: ?string}}, role?: string, class?: string, activeCriteria?: string, order?: int}[]
     */
    public function getItems(string $menu = '')
    {
        if (!isset($this->menuitems[$menu])) {
            $items = [];

            foreach ($this->extensions as $extension) {
                foreach ($extension->getMenuItems($menu) as $item) {
                    $title = $item['title'];
                    $items[$title] = array_merge($items[$title] ?? [], $item);
                }
            }

            usort($items, function ($a, $b) {
                if (array_key_exists('order', $a) && array_key_exists('order', $b)) {
                    return $a['order'] <=> $b['order'];
                } elseif (array_key_exists('order', $b)) {
                    return -$b['order'];
                } elseif (array_key_exists('order', $a)) {
                    return $a['order'];
                } else {
                    return $a['title'] <=> $b['title'];
                }
            });

            $this->menuitems[$menu] = $items;
        }

        return $this->menuitems[$menu];
    }
}
