<?php

namespace App\Controller\Organise;

use App\Entity\Activity\Activity;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Group\Group;

/**
 * Activity controller.
 *
 * @Route("/organise", name="organise_")
 */
class OrganiseController extends AbstractController
{
    /**
     * Lists all activities.
     *
     * @Route("/{id}", name="index", methods={"GET"})
     */
    public function indexAction(Group $group)
    {
        $em = $this->getDoctrine()->getManager();

        $activities = $em->getRepository(Activity::class)->findBy(['author' => $group]);

        return $this->render('organise/index.html.twig', [
            'group' => $group,
            'activities' => $activities,
        ]);
    }
}
