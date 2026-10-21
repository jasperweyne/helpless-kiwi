<?php

namespace App\Controller\Admin;

use App\Entity\Log\Event;
use App\Log\EventService;
use App\Template\Attribute\MenuItem;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Event controller.
 */
#[Route('/admin/event', name: 'admin_event_')]
class EventController extends AbstractController
{
    /**
     * @var EventService
     */
    private $events;

    public function __construct(EventService $events)
    {
        $this->events = $events;
    }

    /**
     * Lists all events.
     */
    #[MenuItem(title: 'Gebeurtenislog', menu: 'admin', role: 'ROLE_ADMIN')]
    #[Route('/', name: 'index', methods: ['GET'])]
    public function indexAction(Request $request, EntityManagerInterface $em, PaginatorInterface $paginator): Response
    {
        $qb = $em->createQueryBuilder()
            ->select('e')
            ->from(Event::class, 'e')
            ->orderBy('e.time', 'DESC')
        ;

        $pagination = $paginator->paginate(
            $qb,
            $request->query->getInt('page', 1),
            $request->query->getInt('limit', 30)
        );

        /** @var array<Event> $items */
        $items = iterator_to_array($pagination->getItems());

        return $this->render('admin/event/index.html.twig', [
            'log' => $this->events->populateAll($items),
            'pagination' => $pagination,
        ]);
    }
}
