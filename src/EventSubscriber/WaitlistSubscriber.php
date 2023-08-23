<?php

namespace App\EventSubscriber;

use App\Entity\Activity\PriceOption;
use App\Entity\Activity\Registration;
use App\Entity\Activity\WaitlistSpot;
use App\Entity\Security\LocalAccount;
use App\Event\RegistrationAddedEvent;
use App\Event\RegistrationRemovedEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class WaitlistSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private EventDispatcher $dispatcher,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        // return the subscribed events, their methods and priorities
        return [
            RegistrationAddedEvent::class => [
                ['removeWaitlistSpots', 0],
            ],
            RegistrationRemovedEvent::class => [
                ['checkWaitlist', 0],
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

        if ($activity->getDeadline() < new \DateTime('now')) {
            $this->addFromWaitlist($option);
        } elseif ($activity->getStart() < new \DateTime('now')) {
            $this->notifyWaitlist($option);
        }
    }

    private function addFromWaitlist(PriceOption $option): void
    {
        if (false === $spot = $option->getWaitlist()->first()) {
            return; // no one on the waitlist
        }

        assert($spot instanceof WaitlistSpot);
        $registration = new Registration();
        $registration
            ->setOption($option)
            ->setPerson($spot->person)
            ->setActivity($option->getActivity())
        ;

        $this->dispatcher->dispatch(new RegistrationAddedEvent($registration));
    }

    private function notifyWaitlist(PriceOption $option): void
    {
        // todo
    }
}
