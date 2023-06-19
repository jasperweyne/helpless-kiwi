<?php

namespace App\EventSubscriber;

use App\Event\Security\CreateAccountsEvent;
use App\Event\Security\RemoveAccountsEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AccountSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        // return the subscribed events, their methods and priorities
        return [
            CreateAccountsEvent::class => [
                ['persistCreateAccounts', 0],
            ],
            RemoveAccountsEvent::class => [
                ['persistRemoveAccounts', 0],
            ],
        ];
    }

    public function persistCreateAccounts(CreateAccountsEvent $event): void
    {
        foreach ($event->accounts as $account) {
            $this->em->persist($account);
        }

        $this->em->flush();
    }

    public function persistRemoveAccounts(RemoveAccountsEvent $event): void
    {
        foreach ($event->accounts as $account) {
            $this->em->remove($account);
        }

        $this->em->flush();
    }
}
