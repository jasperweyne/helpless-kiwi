<?php

namespace App\Controller\Activity;

use App\Calendar\CalendarProvider;
use App\Entity\Activity\Activity;
use App\Entity\Security\LocalAccount;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Calendar controller.
 */
#[Route('/ical', name: 'ical_')]
class CalendarController extends AbstractController
{
    public function __construct(
        protected EntityManagerInterface $em
    ) {
        $this->em = $em;
    }

    #[Route('/', methods: ['GET'], name: 'public')]
    public function getCalendar(
        CalendarProvider $iCalProvider
    ): Response {
        /** @var Activity[] $publicActivities */
        $publicActivities = $this->em->getRepository(Activity::class)->findVisibleUpcomingByGroup([]);

        return new Response($iCalProvider->calendarFeed($publicActivities));
    }

    #[Route('/personal/{calendarToken}', methods: ['GET'], name: 'personal')]
    public function getPersonalCalendar(
        CalendarProvider $iCalProvider,
        LocalAccount $user
    ): Response {
        /** @var Activity[] $personalActivities */
        $personalActivities = $this->em->getRepository(Activity::class)->findRegisteredFor($user);

        return new Response($iCalProvider->calendarFeed($personalActivities));
    }

    #[IsGranted(new Expression(
        'is_authenticated()'
    ))]
    #[Route('/renew', methods: ['POST'], name: 'renew')]
    public function postPersonalCalendarRenew(Request $request): Response
    {
        /** @var LocalAccount $user */
        $user = $this->getUser();
        $user->renewCalendarToken();
        $this->em->flush();
        $this->addFlash('success', 'Je persoonlijke kalender link is vernieuwd');
        $route = $request->headers->get('referer') ?? '/';

        return $this->redirect($route);
    }
}
