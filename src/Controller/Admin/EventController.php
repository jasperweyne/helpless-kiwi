<?php

namespace App\Controller\Admin;

use App\Template\Annotation\MenuItem;
use App\Entity\Log\Event;
use App\Log\EventService;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

/**
 * Event controller.
 *
 * @Route("/admin/event", name="admin_event_")
 */
class EventController extends AbstractController
{
    private $events;

    public function __construct(EventService $events)
    {
        $this->events = $events;
    }

    /**
     * Lists all events.
     *
     * @MenuItem(title="Gebeurtenislog", menu="admin")
     * @Route("/", name="index", methods={"GET"})
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $qb = $em->createQueryBuilder()
            ->select('e')
            ->from(Event::class, 'e')
            ->orderBy('e.time', 'DESC')
        ;

        $pagination = $this->paginate($qb, $request->query->getInt('index'), $request->query->getInt('results', 30));

        return $this->render('admin/event/index.html.twig', [
            'log' => $this->events->populateAll($pagination['results']),
            'pagination' => $pagination,
        ]);
    }

    private function paginate(QueryBuilder $qb, int $index = 0, int $limit = 30)
    {
        $index = \max(0, $index);
        $limit = \max(1, $limit);

        $cqb = clone $qb;
        $count = current($cqb
                    ->select('count('.$qb->getRootAlias().')')
                    ->getQuery()
                    ->getOneOrNullResult()
                );

        $rqb = clone $qb;
        $results = $rqb
            ->setFirstResult($index)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;

        $prev = $index > 0 ? \max(0, $index - $limit) : null;
        $next = $index + $limit < $count ? $index + $limit : null;

        return [
            'total' => $count,
            'results' => $results,
            'hasPrev' => !\is_null($prev),
            'hasNext' => !\is_null($next),
            'prev' => $prev,
            'index' => $index,
            'next' => $next,
        ];
    }
}
