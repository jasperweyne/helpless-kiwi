<?php

namespace App\EventSubscriber;

use App\Calendar\ICalProvider;
use App\Entity\Security\LocalAccount;
use App\Event\RegistrationAddedEvent;
use App\Event\RegistrationRemovedEvent;
use App\Event\Security\CreateAccountsEvent;
use App\Mail\Attachment;
use App\Mail\MailService;
use App\Security\PasswordResetService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Security;

class MailNotificationSubscriber implements EventSubscriberInterface
{
    /**
     * @var ?LocalAccount
     */
    private $user;

    public function __construct(
        private \Twig\Environment $template,
        private MailService $mailer,
        private ICalProvider $calendar,
        private PasswordResetService $passwordResetService,
        Security $security,
    ) {
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
            CreateAccountsEvent::class => [
                ['notifyCreateAccount', -10],
            ]
        ];
    }

    public function notifyRegistrationAdded(RegistrationAddedEvent $event): void
    {
        // no e-mail for reserve registrations
        if ($event->getRegistration()->isReserve()) {
            return;
        }

        $activity = $event->getRegistration()->getActivity();
        assert(null !== $activity);

        $ics = new Attachment(
            $this->calendar->icalSingle($activity),
            $activity->getName().'.ics',
            'text/calendar'
        );

        $confirmation = $event->getRegistration()->getPerson() === $this->user ? 'bevestiging' : 'bericht';
        $title = "Aanmeld$confirmation ".$activity->getName();
        $participant = $event->getRegistration()->getPerson();
        assert(null !== $participant);
        $this->mailer->message(
            [$participant],
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
        assert(null !== $activity);

        $confirmation = $event->getRegistration()->getPerson() === $this->user ? 'bevestiging' : 'bericht';
        $title = "Afmeld$confirmation ".$activity->getName();
        $participant = $event->getRegistration()->getPerson();
        assert(null !== $participant);
        $this->mailer->message(
            [$participant],
            $title,
            $this->template->render('email/removedregistration.html.twig', [
                'person' => $event->getRegistration()->getPerson(),
                'activity' => $activity,
                'title' => $title,
                'by' => $this->user,
            ])
        );
    }

    public function notifyCreateAccount(CreateAccountsEvent $event): void
    {
        foreach ($event->accounts as $account) {
            // don't notify oidc-registered accounts
            if (null !== $account->getOidc()) {
                continue;
            }

            // generate a token that doesn't expire
            $token = $this->passwordResetService->generatePasswordRequestToken($account, false);
            $account->setPasswordRequestedAt(null);

            // send an email
            $this->mailer->message(
                $account,
                'Jouw account',
                $this->template->render('email/newaccount.html.twig', [
                    'name' => $account->getGivenName(),
                    'account' => $account,
                    'token' => $token,
                ])
            );
        }
    }
}
