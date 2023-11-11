<?php

namespace App\EventSubscriber;

use App\Calendar\ICalProvider;
use App\Entity\Security\LocalAccount;
use App\Event\RegistrationAddedEvent;
use App\Event\RegistrationRemovedEvent;
use App\Event\Security\CreateAccountsEvent;
use App\Security\PasswordResetService;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Security\Core\Security;

class MailNotificationSubscriber implements EventSubscriberInterface
{
    /**
     * @var ?LocalAccount
     */
    private $user;

    public function __construct(
        private MailerInterface $mailer,
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
            ],
        ];
    }

    public function notifyRegistrationAdded(RegistrationAddedEvent $event): void
    {
        $activity = $event->getRegistration()->getActivity();
        assert(null !== $activity);

        $confirmation = $event->getRegistration()->getPerson() === $this->user ? 'bevestiging' : 'bericht';
        $title = "Aanmeld$confirmation ".$activity->getName();
        $participant = $event->getRegistration()->getPerson();
        assert(null !== $participant);

        assert(is_string($participant->getEmail()));
        $this->mailer->send((new TemplatedEmail())
            ->to($participant->getEmail())
            ->subject($title)
            ->htmlTemplate('email/newregistration.html.twig')
            ->context([
                'person' => $event->getRegistration()->getPerson(),
                'activity' => $activity,
                'title' => $title,
                'by' => $this->user,
            ])
            ->attach($this->calendar->icalSingle($activity), $activity->getName().'.ics', 'text/calendar')
        );
    }

    public function notifyRegistrationRemoved(RegistrationRemovedEvent $event): void
    {
        $activity = $event->getRegistration()->getActivity();
        assert(null !== $activity);

        $confirmation = $event->getRegistration()->getPerson() === $this->user ? 'bevestiging' : 'bericht';
        $title = "Afmeld$confirmation ".$activity->getName();
        $participant = $event->getRegistration()->getPerson();
        assert(null !== $participant);

        assert(is_string($participant->getEmail()));
        $this->mailer->send((new TemplatedEmail())
            ->to($participant->getEmail())
            ->subject($title)
            ->htmlTemplate('email/removedregistration.html.twig')
            ->context([
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
            // don't notify accounts that can already login
            if (null !== $account->getOidc() || null !== $account->getPassword()) {
                continue;
            }

            // generate a token that doesn't expire
            $token = $this->passwordResetService->generatePasswordRequestToken($account, false);
            $account->setPasswordRequestedAt(null);

            // send an email
            assert(is_string($account->getEmail()));
            $this->mailer->send((new TemplatedEmail())
                ->to($account->getEmail())
                ->subject('Jouw account')
                ->htmlTemplate('email/newaccount.html.twig')
                ->context([
                    'name' => $account->getGivenName(),
                    'account' => $account,
                    'token' => $token,
                ])
            );
        }
    }
}
