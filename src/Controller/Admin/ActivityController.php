<?php

namespace App\Controller\Admin;

use App\Template\Annotation\MenuItem;
use App\Entity\Activity\Activity;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Activity controller.
 *
 * @Route("/admin/activity")
 */
class ActivityController extends AbstractController
{
    /**
     * Lists all activities.
     *
     * @MenuItem(title="Activiteiten")
     * @Route("/", name="admin_activity_index", methods={"GET"})
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $activities = $em->getRepository(Activity::class)->findAll();

        return $this->render('admin/activity/index.html.twig', [
            'activities' => $activities,
        ]);
    }
}
