<?php

namespace App\Controller\Organize;

use App\Template\Annotation\MenuItem;
use App\Entity\Activity\Activity;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Activity controller.
 *
 * @Route("/organize", name="organize_")
 */
class OrganizeController extends AbstractController
{
    /**
     * Lists all activities.
     *
     * @MenuItem(title="Organiseren")
     * @Route("/", name="index", methods={"GET"})
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $activities = $em->getRepository(Activity::class)->findAll();

        return $this->render('organize/index.html.twig', [
            'activities' => $activities,
        ]);
    }

    /**
     * Finds and displays a activity entity.
     *
     * @Route("/{id}", name="show", methods={"GET"})
     */
    public function organizeAction(Activity $activity)
    {
        $em = $this->getDoctrine()->getManager();

        $forms = [];
        foreach ($activity->getOptions() as $option) {
            $forms[] = [
                'data' => $option,
                'form' => $this->singleRegistrationForm($option)->createView(),
            ];
        }

        return $this->render('organize/show.html.twig', [
            'activity' => $activity,
            'options' => $forms,
        ]);
    }
}
