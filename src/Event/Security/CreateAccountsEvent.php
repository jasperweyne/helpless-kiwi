<?php

namespace App\Event\Security;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * The accounst.create event is dispatched for new accounts to be registered.
 */
class CreateAccountsEvent extends Event
{
    public const NAME = 'accounts.create';

    public function __construct(
        /** @var \App\Entity\Security\LocalAccount[] */
        public readonly array $accounts,
    ) {
    }
}
