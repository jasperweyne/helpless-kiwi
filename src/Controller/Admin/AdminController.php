<?php

namespace App\Controller\Admin;

use App\Template\Annotation\MenuItem;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Activity controller.
 *
 * @Route("/admin", name="admin_")
 */
class AdminController extends AbstractController
{
    /**
     * Lists all activities.
     *
     * @MenuItem(title="Overzicht", menu="admin", activeCriteria="admin_index", order=-1)
     * @Route("/", name="index", methods={"GET"})
     */
    public function indexAction()
    {
        return $this->render('admin/index.html.twig');
    }
}
