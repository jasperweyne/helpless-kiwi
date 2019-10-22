<?php

namespace App\Template;

interface MenuExtensionInterface
{
    /**
     * Returns a list of available menu items.
     *
     * @return array
     */
    public function getMenuItems(string $menu);
}
