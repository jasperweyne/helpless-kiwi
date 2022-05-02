<?php

namespace App\Group;

use App\Entity\Group\Group;
use App\Template\MenuExtensionInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class OrganiseMenuExtension implements MenuExtensionInterface
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
     * @var array{title: string, path: array{0: ?string, 1: array{id: ?string}}}[]
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
     * @return array{title: string, path: array{0: ?string, 1: array{id: ?string}}, role?: string, class?: string, activeCriteria?: string, order?: int}[]
     */
    public function getMenuItems(string $menu = ''): array
    {
        if ('' !== $menu) {
            return [];
        }

        if (!$this->menuItems) {
            $this->discoverMenuItems();
        }

        return $this->menuItems;
    }

    /**
     * Discovers menu items.
     */
    private function discoverMenuItems(): void
    {
        $this->menuItems = [];

        if (null != $this->getUser()) {
            $groups = $this->em->getRepository(Group::class)->findAllFor($this->getUser());

            foreach ($groups as $group) {
                if (!$group->isActive()) {
                    continue;
                }

                $this->menuItems[] = [
                    'title' => $group->getName(),
                    'path' => ['organise_index', ['id' => $group->getId()]],
                ];
            }
        }
    }

    /**
     * @return UserInterface|\Stringable|null
     */
    private function getUser()
    {
        if (null === $token = $this->tokenStorage->getToken()) {
            return null;
        }

        if (!\is_object($user = $token->getUser())) {
            // e.g. anonymous authentication
            return null;
        }

        return $user;
    }
}
