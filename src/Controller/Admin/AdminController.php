<?php

namespace App\Controller\Admin;

use App\Template\Annotation\MenuItem;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
    public function indexAction(): Response
    {
        return $this->render('admin/index.html.twig');
    }
}
