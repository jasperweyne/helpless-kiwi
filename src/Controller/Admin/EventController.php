<?php

namespace App\Controller\Admin;

use App\Template\Annotation\MenuItem;
use App\Entity\Log\BaseEvent;
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
     * @MenuItem(title="Gebeurtenislog")
     * @Route("/", name="index", methods={"GET"})
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $log = $em->getRepository(BaseEvent::class)->findAll();

        return $this->render('admin/event/index.html.twig', [
            'log' => $log,
        ]);
    }
}
