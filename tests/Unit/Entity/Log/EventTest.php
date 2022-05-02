<?php

namespace Tests\Unit\Entity\Log;

use App\Entity\Log\Event;
use App\Entity\Security\LocalAccount;
use DateTime;
use ReflectionClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class EventTest.
 *
 * @covers \App\Entity\Log\Event
 */
class EventTest extends KernelTestCase
{
    /**
     * @var Event
     */
    protected $event;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        /* @todo Correctly instantiate tested object to use it. */
        $this->event = new Event();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->event);
    }

    public function testGetId(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(Event::class))
            ->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($this->event, $expected);
        self::assertSame($expected, $this->event->getId());
    }

    public function testGetDiscr(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(Event::class))
            ->getProperty('discr');
        $property->setAccessible(true);
        $property->setValue($this->event, $expected);
        self::assertSame($expected, $this->event->getDiscr());
    }

    public function testSetDiscr(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(Event::class))
            ->getProperty('discr');
        $property->setAccessible(true);
        $this->event->setDiscr($expected);
        self::assertSame($expected, $property->getValue($this->event));
    }

    public function testGetTime(): void
    {
        $expected = new DateTime();
        $property = (new ReflectionClass(Event::class))
            ->getProperty('time');
        $property->setAccessible(true);
        $property->setValue($this->event, $expected);
        self::assertSame($expected, $this->event->getTime());
    }

    public function testSetTime(): void
    {
        $expected = new DateTime();
        $property = (new ReflectionClass(Event::class))
            ->getProperty('time');
        $property->setAccessible(true);
        $this->event->setTime($expected);
        self::assertSame($expected, $property->getValue($this->event));
    }

    public function testGetMeta(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(Event::class))
            ->getProperty('meta');
        $property->setAccessible(true);
        $property->setValue($this->event, $expected);
        self::assertSame($expected, $this->event->getMeta());
    }

    public function testSetMeta(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(Event::class))
            ->getProperty('meta');
        $property->setAccessible(true);
        $this->event->setMeta($expected);
        self::assertSame($expected, $property->getValue($this->event));
    }

    public function testGetPerson(): void
    {
        $expected = new LocalAccount();
        $expected->setEmail('john@doe.eyes');
        $property = (new ReflectionClass(Event::class))
            ->getProperty('person');
        $property->setAccessible(true);
        $property->setValue($this->event, $expected);
        self::assertSame($expected, $this->event->getPerson());
    }

    public function testSetPerson(): void
    {
        $expected = new LocalAccount();
        $expected->setEmail('john@doe.eyes');
        $property = (new ReflectionClass(Event::class))
            ->getProperty('person');
        $property->setAccessible(true);
        $this->event->setPerson($expected);
        self::assertSame($expected, $this->event->getPerson());
    }

    public function testGetObjectId(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(Event::class))
            ->getProperty('objectId');
        $property->setAccessible(true);
        $property->setValue($this->event, $expected);
        self::assertSame($expected, $this->event->getObjectId());
    }

    public function testSetObjectId(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(Event::class))
            ->getProperty('objectId');
        $property->setAccessible(true);
        $this->event->setObjectId($expected);
        self::assertSame($expected, $property->getValue($this->event));
    }

    public function testGetObjectType(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(Event::class))
            ->getProperty('objectType');
        $property->setAccessible(true);
        $property->setValue($this->event, $expected);
        self::assertSame($expected, $this->event->getObjectType());
    }

    public function testSetObjectType(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(Event::class))
            ->getProperty('objectType');
        $property->setAccessible(true);
        $this->event->setObjectType($expected);
        self::assertSame($expected, $property->getValue($this->event));
    }
}
