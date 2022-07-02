<?php

namespace Tests\Unit\Event;

use App\Entity\Activity\Registration;
use App\Event\RegistrationRemovedEvent;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionClass;
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

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        /** @var Registration&MockObject */
        $registration = $this->createMock(Registration::class);
        $this->registrationRemovedEvent = new RegistrationRemovedEvent($registration);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->registrationRemovedEvent);
    }

    public function testGetRegistration(): void
    {
        $expected = $this->createMock(Registration::class);
        $property = (new ReflectionClass(RegistrationRemovedEvent::class))
            ->getProperty('registration');
        $property->setAccessible(true);
        $property->setValue($this->registrationRemovedEvent, $expected);
        self::assertSame($expected, $this->registrationRemovedEvent->getRegistration());
    }
}
