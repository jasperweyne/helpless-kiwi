<?php

namespace App\Template;

/**
 * @phpstan-type MenuItemArray array{
 *  title: string,
 *  path?: string|array{0: ?string, 1: array<string, string>},
 *  role?: string,
 *  class?: string,
 *  activeCriteria?: string,
 *  order?: int,
 *  sub?: array{
 *    title: string,
 *    path: string|array{0: ?string, 1: array<string, string>}
 *  }[]
 * }
 */
interface MenuExtensionInterface
{
    /**
     * Returns a list of available menu items.
     *
     * @return MenuItemArray[]
     */
    public function getMenuItems(string $menu);
}
