<?php

namespace App\Controller\Admin;

use App\Template\Annotation\MenuItem;
use App\Entity\Log\BaseEvent;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Event controller.
 *
 * @Route("/admin/event")
 */
class EventController extends AbstractController
{
    /**
     * Lists all events.
     *
     * @MenuItem(title="Gebeurtenislog")
     * @Route("/", name="admin_event_index", methods={"GET"})
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
