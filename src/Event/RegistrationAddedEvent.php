<?php

namespace App\Event;

use App\Entity\Activity\Registration;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * The registration.added event is dispatched each time an person is
 * registered for an activity.
 */
class RegistrationAddedEvent extends Event
{
    public const NAME = 'registration.added';

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
