<?php

namespace App\Template;

class MenuBuilder
{
    private $extensions;

    private $menuitems;

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
     * @return array
     */
    public function getItems(string $menu = '')
    {
        if (!isset($this->menuitems[$menu])) {
            $items = [];

            foreach ($this->extensions as $extension) {
                $items = array_merge($items, $extension->getMenuItems($menu));
            }

            $this->menuitems[$menu] = $items;
        }

        return $this->menuitems[$menu];
    }
}
