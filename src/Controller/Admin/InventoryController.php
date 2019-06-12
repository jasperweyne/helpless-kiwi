<?php

namespace App\Controller\Admin;

use App\Template\Annotation\MenuItem;
use App\Entity\Inventory\Item;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Inventory controller.
 *
 * @Route("/admin/inventory")
 */
class InventoryController extends AbstractController
{
    /**
     * Lists all items.
     *
     * @MenuItem(title="Inventaris")
     * @Route("/", name="admin_inventory_index", methods={"GET"})
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $items = $em->getRepository(Item::class)->findBy([], ['title' => 'ASC']);

        return $this->render('admin/inventory/index.html.twig', [
            'items' => $items,
        ]);
    }
}
