<?php

namespace App\EventSubscriber;

use App\Entity\Activity\ExternalRegistrant;
use App\Entity\Security\ContactInterface;
use App\Entity\Security\LocalAccount;
use App\Event\RegistrationAddedEvent;
use App\Event\RegistrationRemovedEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;
use Symfony\Component\Security\Core\Security;

class RegistrationSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private RequestStack $stack,
        private Security $security
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        // return the subscribed events, their methods and priorities
        return [
            RegistrationAddedEvent::class => [
                ['persistRegistrationAdded', 0],
            ],
            RegistrationRemovedEvent::class => [
                ['persistRegistrationRemoved', 0],
            ],
        ];
    }

    public function persistRegistrationAdded(RegistrationAddedEvent $event): void
    {
        $registration = $event->getRegistration();

        $this->em->persist($registration);
        $this->em->flush();

        $name = '';
        $registrant = $registration->getPerson();
        assert($registrant instanceof ContactInterface);
        if ($registrant->getName() !== $this->getUser()->getName()) {
            $name = ' van '.$registrant->getName();
        }

        $this->getFlashbag()->add('success', 'Aanmelding'.$name.' gelukt!');
    }

    public function persistRegistrationRemoved(RegistrationRemovedEvent $event): void
    {
        $registration = $event->getRegistration();

        if ($registration->getPerson() instanceof ExternalRegistrant) {
            $this->em->remove($registration);
        } else {
            $registration->setDeleteDate(new \DateTime('now'));
        }

        $this->em->flush();

        $name = '';
        $registrant = $registration->getPerson();
        assert($registrant instanceof ContactInterface);
        if ($registrant->getName() !== $this->getUser()->getName()) {
            $name = ' van '.$registrant->getName();
        }
        $this->getFlashbag()->add('success', 'Afmelding'.$name.' gelukt!');
    }

    private function getFlashbag(): FlashBagInterface
    {
        $session = $this->stack->getSession();
        assert($session instanceof FlashBagAwareSessionInterface);

        return $session->getFlashBag();
    }

    private function getUser(): LocalAccount
    {
        $user = $this->security->getUser();
        assert($user instanceof LocalAccount);

        return $user;
    }
}
