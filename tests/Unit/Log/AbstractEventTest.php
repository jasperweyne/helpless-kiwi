<?php

namespace Tests\Unit\Log;

use App\Log\AbstractEvent;
use DateTime;
use ReflectionClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class AbstractEventTest.
 *
 * @covers \App\Log\AbstractEvent
 */
class AbstractEventTest extends KernelTestCase
{
    /**
     * @var AbstractEvent
     */
    protected $abstractEvent;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        /* @todo Correctly instantiate tested object to use it. */
        $this->abstractEvent = new AbstractEvent();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->abstractEvent);
    }

    public function testGetTime(): void
    {
        $expected = new DateTime();
        $property = (new ReflectionClass(AbstractEvent::class))
            ->getProperty('time');
        $property->setAccessible(true);
        $property->setValue($this->abstractEvent, $expected);
        $this->assertSame($expected, $this->abstractEvent->getTime());
    }

    public function testGetPersonId(): void
    {
        /* @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testGetEntity(): void
    {
        $expected = new DateTime();
        $property = (new ReflectionClass(AbstractEvent::class))
            ->getProperty('entity');
        $property->setAccessible(true);
        $property->setValue($this->abstractEvent, $expected);
        $this->assertSame($expected, $this->abstractEvent->getEntity());
    }

    public function testSetEntity(): void
    {
        $expected = new \stdClass();
        $property = (new ReflectionClass(AbstractEvent::class))
            ->getProperty('entity');
        $property->setAccessible(true);
        $this->abstractEvent->setEntity($expected);
        $this->assertSame($expected, $property->getValue($this->abstractEvent));
    }

    public function testGetEntityType(): void
    {
        $expected = new \stdClass();
        $property = (new ReflectionClass(AbstractEvent::class))
            ->getProperty('entityType');
        $property->setAccessible(true);
        $property->setValue($this->abstractEvent, $expected);
        $this->assertSame($expected, $this->abstractEvent->getEntityType());
    }

    public function testSetEntityType(): void
    {
        $expected = null;
        $property = (new ReflectionClass(AbstractEvent::class))
            ->getProperty('entityType');
        $property->setAccessible(true);
        $this->abstractEvent->setEntityType($expected);
        $this->assertSame($expected, $property->getValue($this->abstractEvent));
    }

    public function testGetTitle(): void
    {
        /* @todo This test is incomplete. */
        $this->markTestIncomplete();
    }
}
