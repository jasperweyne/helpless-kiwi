<?php

namespace App\Template;

interface MenuExtensionInterface
{
    /**
     * Returns a list of available menu items.
     *
     * @return array{title: string, path: string|array{0: ?string, 1: array{id: ?string}}, role?: string, class?: string, activeCriteria?: string, order?: int}[]
     */
    public function getMenuItems(string $menu);
}
