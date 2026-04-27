<?php

namespace Tests\Unit\Event;

use App\Entity\Activity\Registration;
use App\Event\RegistrationAddedEvent;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class RegistrationAddedEventTest.
 *
 * @covers \App\Event\RegistrationAddedEvent
 */
final class RegistrationAddedEventTest extends KernelTestCase
{
    /**
     * @var RegistrationAddedEvent
     */
    private $registrationAddedEvent;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var Registration&MockObject */
        $registration = $this->createMock(Registration::class);
        $this->registrationAddedEvent = new RegistrationAddedEvent($registration);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->registrationAddedEvent);
    }
}
