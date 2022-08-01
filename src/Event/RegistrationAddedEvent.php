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

    /**
     * @var Registration
     */
    protected $registration;

    public function __construct(Registration $registration)
    {
        $this->registration = $registration;
    }

    public function getRegistration(): Registration
    {
        return $this->registration;
    }
}
