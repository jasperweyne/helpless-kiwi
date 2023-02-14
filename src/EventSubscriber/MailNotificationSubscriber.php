<?php

namespace App\EventSubscriber;

use App\Calendar\ICalProvider;
use App\Entity\Security\LocalAccount;
use App\Event\RegistrationAddedEvent;
use App\Event\RegistrationRemovedEvent;
use App\Mail\Attachment;
use App\Mail\MailService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Security;

class MailNotificationSubscriber implements EventSubscriberInterface
{
    /**
     * @var \Twig\Environment
     */
    private $template;

    /**
     * @var MailService
     */
    private $mailer;

    /**
     * @var ICalProvider
     */
    private $calendar;

    /**
     * @var ?LocalAccount
     */
    private $user;

    public function __construct(
        \Twig\Environment $template,
        MailService $mailer,
        ICalProvider $calendar,
        Security $security
    ) {
        $this->template = $template;
        $this->mailer = $mailer;
        $this->calendar = $calendar;

        $user = $security->getUser();
        assert($user instanceof LocalAccount);
        $this->user = $user;
    }

    public static function getSubscribedEvents(): array
    {
        // return the subscribed events, their methods and priorities
        return [
            RegistrationAddedEvent::class => [
                ['notifyRegistrationAdded', -10],
            ],
            RegistrationRemovedEvent::class => [
                ['notifyRegistrationRemoved', -10],
            ],
        ];
    }

    public function notifyRegistrationAdded(RegistrationAddedEvent $event): void
    {
        // no e-mail for reserve registrations
        if ($event->getRegistration()->isReserve()) {
            return;
        }

        $activity = $event->getRegistration()->getActivity();
        assert($activity !== null);

        $ics = new Attachment(
            $this->calendar->icalSingle($activity),
            $activity->getName().'.ics',
            'text/calendar'
        );

        $confirmation = $event->getRegistration()->getPerson() === $this->user ? 'bevestiging' : 'bericht';
        $title = "Aanmeld$confirmation ".$activity->getName();
        $organizer = $event->getRegistration()->getPerson();
        assert($organizer !== null);
        $this->mailer->message(
            [$organizer],
            $title,
            $this->template->render('email/newregistration.html.twig', [
                'person' => $event->getRegistration()->getPerson(),
                'activity' => $activity,
                'title' => $title,
                'by' => $this->user,
            ]),
            [$ics]
        );
    }

    public function notifyRegistrationRemoved(RegistrationRemovedEvent $event): void
    {
        // no e-mail for reserve registrations
        if ($event->getRegistration()->isReserve()) {
            return;
        }

        $activity = $event->getRegistration()->getActivity();
        assert($activity !== null);

        $confirmation = $event->getRegistration()->getPerson() === $this->user ? 'bevestiging' : 'bericht';
        $title = "Afmeld$confirmation ".$activity->getName();
        $organizer = $event->getRegistration()->getPerson();
        assert($organizer !== null);
        $this->mailer->message(
            [$organizer],
            $title,
            $this->template->render('email/removedregistration.html.twig', [
                'person' => $event->getRegistration()->getPerson(),
                'activity' => $activity,
                'title' => $title,
                'by' => $this->user,
            ])
        );
    }
}
