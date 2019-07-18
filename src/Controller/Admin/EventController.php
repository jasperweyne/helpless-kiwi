<?php

namespace App\Controller\Admin;

use App\Template\Annotation\MenuItem;
use App\Entity\Log\Event;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Event controller.
 *
 * @Route("/admin/event", name="admin_event_")
 */
class EventController extends AbstractController
{
    /**
     * Lists all events.
     *
     * @MenuItem(title="Gebeurtenislog", menu="admin")
     * @Route("/", name="index", methods={"GET"})
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $log = $em->getRepository(Event::class)->findAll();

        return $this->render('admin/event/index.html.twig', [
            'log' => $log,
        ]);
    }
}
