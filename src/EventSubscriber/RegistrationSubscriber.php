<?php

namespace App\EventSubscriber;

use App\Entity\Security\ContactInterface;
use App\Entity\Security\LocalAccount;
use App\Event\RegistrationAddedEvent;
use App\Event\RegistrationRemovedEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Security;

class RegistrationSubscriber implements EventSubscriberInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var FlashBagInterface
     */
    private $flash;

    /**
     * @var LocalAccount
     */
    private $user;

    public function __construct(
        EntityManagerInterface $em,
        FlashBagInterface $flash,
        Security $security
    ) {
        $this->em = $em;
        $this->flash = $flash;

        $user = $security->getUser();
        assert($user instanceof LocalAccount);
        $this->user = $user;
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
        $this->em->persist($event->getRegistration());
        $this->em->flush();

        $name = '';
        $registrant = $event->getRegistration()->getPerson();
        assert($registrant instanceof ContactInterface);
        if ($registrant->getName() !== $this->user->getName()) {
            $name = ' van ' . $registrant->getName();
        }
        $location = $event->getRegistration()->isReserve() ? ' op de reservelijst!' : ' gelukt!';

        $this->flash->add('success', 'Aanmelding' . $name  . $location);
    }

    public function persistRegistrationRemoved(RegistrationRemovedEvent $event): void
    {
        $event->getRegistration()->setDeleteDate(new \DateTime('now'));
        $this->em->flush();

        $name = '';
        $registrant = $event->getRegistration()->getPerson();
        assert($registrant instanceof ContactInterface);
        if ($registrant->getName() !== $this->user->getName()) {
            $name = ' van ' . $registrant->getName();
        }
        $this->flash->add('success', 'Afmelding' . $name  .' gelukt!');
    }
}
