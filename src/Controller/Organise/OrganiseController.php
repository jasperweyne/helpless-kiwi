<?php

namespace App\Controller\Organise;

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
 * @Route("/organise", name="organise_")
 */
class OrganiseController extends AbstractController
{
    /**
     * Lists all activities.
     *
     * @MenuItem(title="Organiseren", role="ROLE_DISABLED")
     * @Route("/", name="index", methods={"GET"})
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $activities = $em->getRepository(Activity::class)->findAll();

        return $this->render('organise/index.html.twig', [
            'activities' => $activities,
        ]);
    }
}
