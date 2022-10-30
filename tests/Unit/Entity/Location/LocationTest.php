<?php

namespace Tests\Unit\Entity\Location;

use App\Entity\Activity\Activity;
use App\Entity\Location\Location;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class LocationTest.
 *
 * @covers \App\Entity\Location\Location
 */
class LocationTest extends KernelTestCase
{
    /**
     * @var Location
     */
    protected $location;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        /* @todo Correctly instantiate tested object to use it. */
        $this->location = new Location();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->location);
    }

    public function testGetId(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(Location::class))
            ->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($this->location, $expected);
        self::assertSame($expected, $this->location->getId());
    }

    public function testSetId(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(Location::class))
            ->getProperty('id');
        $property->setAccessible(true);
        $this->location->setId($expected);
        self::assertSame($expected, $property->getValue($this->location));
    }

    public function testGetAddress(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(Location::class))
            ->getProperty('address');
        $property->setAccessible(true);
        $property->setValue($this->location, $expected);
        self::assertSame($expected, $this->location->getAddress());
    }

    public function testSetAddress(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(Location::class))
            ->getProperty('address');
        $property->setAccessible(true);
        $this->location->setAddress($expected);
        self::assertSame($expected, $property->getValue($this->location));
    }

    public function testGetActivities(): void
    {
        $expected = new ArrayCollection();
        $property = (new ReflectionClass(Location::class))
            ->getProperty('activities');
        $property->setAccessible(true);
        $property->setValue($this->location, $expected);
        self::assertSame($expected, $this->location->getActivities());
    }

    public function testAddActivity(): void
    {
        /** @var MockObject&Activity */
        $expected = $this->createMock(Activity::class);
        $expected->expects(self::once())->method('setLocation')->with($this->location);
        $property = (new ReflectionClass(Location::class))
            ->getProperty('activities');
        $property->setAccessible(true);
        $this->location->addActivity($expected);
        self::assertContains($expected, $property->getValue($this->location));
    }

    public function testRemoveActivity(): void
    {
        /** @var MockObject&Activity */
        $expected = $this->createMock(Activity::class);
        $expected->method('getLocation')->willReturn($this->location);
        $expected->expects(self::once())->method('setLocation')->with(null);
        $property = (new ReflectionClass(Location::class))
            ->getProperty('activities');
        $property->setAccessible(true);
        $property->getValue($this->location)->add($expected);
        $this->location->removeActivity($expected);
        self::assertNotContains($expected, $property->getValue($this->location));
    }
}
