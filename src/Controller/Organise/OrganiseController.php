<?php

namespace App\Controller\Organise;

use App\Template\Annotation\MenuItem;
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
     * @MenuItem(title="Organiseren", role="ROLE_DISABLED")
     * @Route("/{id}", name="index", methods={"GET"})
     */
    public function indexAction(Group $group)
    {
        $em = $this->getDoctrine()->getManager();

        $activities = $em->getRepository(Activity::class)->findAll();

        return $this->render('organise/index.html.twig', [
            'activities' => $activities,
        ]);
    }
}
