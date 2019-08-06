<?php

namespace App\Controller\Activity;

use App\Entity\Activity\Activity;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Activity controller.
 *
 * @Route("/", name="activity_")
 */
class ActivityController extends AbstractController
{
    /**
     * Lists all activities.
     *
     * @Route("/", name="index", methods={"GET"})
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $activities = $em->getRepository(Activity::class)->findAll();

        return $this->render('activity/index.html.twig', [
            'activities' => $activities,
        ]);
    }

    /**
     * Finds and displays a activity entity.
     *
     * @Route("/activity/{id}", name="show", methods={"GET"})
     */
    public function showAction(Activity $activity)
    {
        $em = $this->getDoctrine()->getManager();

        return $this->render('activity/show.html.twig', [
            'activity' => $activity,
        ]);
    }
}
