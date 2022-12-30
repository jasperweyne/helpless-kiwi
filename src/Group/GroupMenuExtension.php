<?php

namespace App\Group;

use App\Entity\Group\Group;
use App\Entity\Security\LocalAccount;
use App\Repository\GroupRepository;
use App\Template\MenuExtensionInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @phpstan-import-type MenuItemArray from MenuExtensionInterface
 */
class GroupMenuExtension implements MenuExtensionInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

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
    public function __construct(EntityManagerInterface $em, TokenStorageInterface $tokenStorage)
    {
        $this->em = $em;
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

        if (null != $this->getUser()) {
            /** @var GroupRepository */
            $groupRepo = $this->em->getRepository(Group::class);
            $groups = $groupRepo->findAllFor($this->getUser());

            /** @var Group $group */
            foreach ($groups as $group) {
                if (true !== $group->isActive() || null === $group->getName()) {
                    continue;
                }

                $this->menuItems[] = [
                    'title' => $group->getName(),
                    'path' => ['admin_activity_group', [
                        'id' => $group->getId() ?? '',
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
