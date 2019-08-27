<?php

namespace App\Controller\Admin;

use App\Template\Annotation\MenuItem;
use App\Entity\Group\Taxonomy;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Group controller.
 *
 * @Route("/admin/group", name="admin_group_")
 */
class GroupController extends AbstractController
{
    /**
     * Lists all groups.
     *
     * @MenuItem(title="Groepen", menu="admin", role="ROLE_DISABLED")
     * @Route("/", name="index", methods={"GET"})
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $groups = $em->getRepository(Taxonomy::class)->findAll();

        return $this->render('admin/group/index.html.twig', [
            'groups' => $groups,
        ]);
    }
}
