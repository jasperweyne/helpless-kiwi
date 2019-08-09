<?php

namespace App\Controller\Organize;

use App\Template\Annotation\MenuItem;
use App\Entity\Activity\Activity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Activity\PriceOption;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use App\Entity\Activity\Registration;

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
