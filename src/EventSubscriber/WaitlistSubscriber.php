<?php

namespace App\EventSubscriber;

use App\Entity\Activity\PriceOption;
use App\Entity\Activity\Registration;
use App\Entity\Activity\WaitlistSpot;
use App\Entity\Security\LocalAccount;
use App\Event\RegistrationAddedEvent;
use App\Event\RegistrationRemovedEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\MailerInterface;

class WaitlistSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private EventDispatcherInterface $dispatcher,
        private MailerInterface $mailer,
        private \Twig\Environment $template,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        // return the subscribed events, their methods and priorities
        return [
            RegistrationAddedEvent::class => [
                ['removeWaitlistSpots', 10],
            ],
            RegistrationRemovedEvent::class => [
                ['checkWaitlist', 10],
            ],
        ];
    }

    public function removeWaitlistSpots(RegistrationAddedEvent $event): void
    {
        $registration = $event->getRegistration();
        $person = $registration->getPerson();

        if (!($person instanceof LocalAccount)) {
            return;
        }

        $removals = $this->em->getRepository(WaitlistSpot::class)->findBy([
            'option' => $registration->getOption(),
            'person' => $person,
        ]);

        foreach ($removals as $spot) {
            $this->em->remove($spot);
        }
    }

    public function checkWaitlist(RegistrationRemovedEvent $event): void
    {
        $option = $event->getRegistration()->getOption();
        if (null === $option || null === ($activity = $option->getActivity())) {
            return;
        }

        // make sure you don't add someone if the activity was over capacity
        /* if ($activity->atCapacity()) { */
        /*     return; */
        /* } */

        if ($activity->getDeadline() > new \DateTime('now')) {
            $this->addFromWaitlist($option);
        } elseif ($activity->getStart() > new \DateTime('now')) {
            $this->notifyWaitlist($option);
        }
    }

    private function addFromWaitlist(PriceOption $option): void
    {
        if (false === $spot = $option->getWaitlist()->first()) {
            return; // no one on the waitlist
        }

        $registration = new Registration();
        $registration
            ->setOption($option)
            ->setPerson($spot->person)
            ->setActivity($option->getActivity())
        ;

        $this->dispatcher->dispatch(new RegistrationAddedEvent($registration, true));
    }

    private function notifyWaitlist(PriceOption $option): void
    {
        foreach ($option->getWaitlist() as $spot) {
            $this->mailer->send((new TemplatedEmail())
                ->to($spot->person)
                ->subject('Er is een ticket aangeboden voor '.($option->getActivity()?->getName() ?? ''))
                ->htmlTemplate('email/waitlist_notify.html.twig')
                ->context([
                    'option' => $option,
                    'person' => $spot->person,
                ])
            );
        }
    }
}
