<?php

namespace App\Group;

use App\Entity\Group\Group;
use App\Entity\Security\Auth;
use App\Template\MenuExtensionInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

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
     * @var array
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
     */
    public function getMenuItems(string $menu = '')
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
    private function discoverMenuItems()
    {

        $this->menuItems = [];

        if ($this->getUser() != null) {
            $groups = $this->em->getRepository(Group::class)->findAllFor($this->getUser()->getPerson());
            
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

    private function getUser(): ?Auth
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
