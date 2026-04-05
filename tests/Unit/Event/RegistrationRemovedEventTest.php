<?php

namespace Tests\Unit\Event;

use App\Entity\Activity\Registration;
use App\Event\RegistrationRemovedEvent;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class RegistrationRemovedEventTest.
 *
 * @covers \App\Event\RegistrationRemovedEvent
 */
final class RegistrationRemovedEventTest extends KernelTestCase
{
    /**
     * @var RegistrationRemovedEvent
     */
    private $registrationRemovedEvent;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var Registration&MockObject */
        $registration = $this->createMock(Registration::class);
        $this->registrationRemovedEvent = new RegistrationRemovedEvent($registration);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->registrationRemovedEvent);
    }
}
