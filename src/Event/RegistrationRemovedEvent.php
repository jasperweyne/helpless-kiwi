<?php

namespace App\Event;

use App\Entity\Activity\Registration;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * The registration.added event is dispatched each time an existing registration
 * is removed from an activity.
 */
class RegistrationRemovedEvent extends Event
{
    public const NAME = 'registration.removed';

    public function __construct(
        public readonly Registration $registration,
        public readonly bool $generated = false,
    ) {
    }

    public function getRegistration(): Registration
    {
        return $this->registration;
    }
}
