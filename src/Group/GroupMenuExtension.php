<?php

namespace App\Group;

use App\Entity\Security\LocalAccount;
use App\Template\MenuExtensionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @phpstan-import-type MenuItemArray from MenuExtensionInterface
 */
class GroupMenuExtension implements MenuExtensionInterface
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var array{title: string, path: array{0: ?string, 1: array<string, string>}}[]
     */
    private $menuItems;

    /**
     * GroupMenuExtension constructor.
     */
    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * Returns all the menu items.
     *
     * @return MenuItemArray[]
     */
    public function getMenuItems(string $menu = '')
    {
        if ('admin' !== $menu) {
            return [];
        }

        if (null === $this->menuItems) {
            $this->discoverMenuItems();
        }

        return [[
            'title' => 'Activiteiten',
            'sub' => $this->menuItems,
        ]];
    }

    /**
     * Discovers menu items.
     */
    private function discoverMenuItems(): void
    {
        $this->menuItems = [];

        if (null !== $user = $this->getUser()) {
            foreach ($user->getRelations() as $group) {
                if (true !== $group->isActive() || null === $group->getName()) {
                    continue;
                }

                $this->menuItems[] = [
                    'title' => $group->getName(),
                    'path' => ['admin_activity_group', [
                        'group' => $group->getId() ?? '',
                    ]],
                ];
            }
        }
    }

    private function getUser(): ?LocalAccount
    {
        if (null === $token = $this->tokenStorage->getToken()) {
            return null;
        }

        if (!\is_object($user = $token->getUser())) {
            // e.g. anonymous authentication
            return null;
        }

        if (!$user instanceof LocalAccount) {
            throw new \LogicException('The user must be an instance of LocalAccount.');
        }

        return $user;
    }
}
