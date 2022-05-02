<?php

namespace App\Group;

use App\Entity\Group\Group;
use App\Template\MenuExtensionInterface;
use Doctrine\ORM\EntityManagerInterface;

class GroupMenuExtension implements MenuExtensionInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var array<string, array{title: ?string, path: array{0: ?string, 1: array{id: ?string}}}[]>
     */
    private $menuItems = [];

    /**
     * GroupMenuExtension constructor.
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Returns all the menu items.
     *
     * @return array{title: ?string, path: array{0: ?string, 1: array{id: ?string}}}[]
     */
    public function getMenuItems(string $menu = '')
    {
        return []; // disable for now

        if (!$this->menuItems) {
            $this->discoverMenuItems();
        }

        if (!array_key_exists($menu, $this->menuItems)) {
            return [];
        }

        return $this->menuItems[$menu];
    }

    /**
     * Discovers menu items.
     */
    private function discoverMenuItems(): void
    {
        $groups = $this->em->getRepository(Group::class)->findBy(['category' => true]);

        $mapped = [];
        foreach ($groups as $group) {
            $mapped[] = [
                'title' => $group->getName(),
                'path' => ['admin_group_show', ['id' => $group->getId()]],
            ];
        }

        $this->menuItems['admin'] = $mapped;
    }
}
