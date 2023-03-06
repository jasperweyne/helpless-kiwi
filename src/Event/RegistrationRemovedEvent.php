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
