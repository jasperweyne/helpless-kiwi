<?php

namespace Tests\Unit\Event;

use App\Entity\Activity\Registration;
use App\Event\RegistrationAddedEvent;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionClass;
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

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        /** @var Registration&MockObject */
        $registration = $this->createMock(Registration::class);
        $this->registrationAddedEvent = new RegistrationAddedEvent($registration);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->registrationAddedEvent);
    }

    public function testGetRegistration(): void
    {
        $expected = $this->createMock(Registration::class);
        $property = (new ReflectionClass(RegistrationAddedEvent::class))
            ->getProperty('registration');
        $property->setAccessible(true);
        $property->setValue($this->registrationAddedEvent, $expected);
        self::assertSame($expected, $this->registrationAddedEvent->getRegistration());
    }
}
