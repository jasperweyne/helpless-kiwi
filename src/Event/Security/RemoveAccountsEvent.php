<?php

namespace App\Event\Security;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * The accounts.remove event is dispatched for accounts to be removed.
 */
class RemoveAccountsEvent extends Event
{
    public const NAME = 'accounts.remove';

    public function __construct(
        /** @var \App\Entity\Security\LocalAccount[] */
        public array $accounts
    ) {
    }
}
