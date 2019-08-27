<?php

namespace App\Controller\Admin;

use App\Template\Annotation\MenuItem;
use App\Entity\Inventory\Item;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Inventory controller.
 *
 * @Route("/admin/inventory", name="admin_inventory_")
 */
class InventoryController extends AbstractController
{
    /**
     * Lists all items.
     *
     * @MenuItem(title="Inventaris", menu="admin", role="ROLE_DISABLED")
     * @Route("/", name="index", methods={"GET"})
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $items = $em->getRepository(Item::class)->findBy([], ['name' => 'ASC']);

        return $this->render('admin/inventory/index.html.twig', [
            'items' => $items,
        ]);
    }
}
