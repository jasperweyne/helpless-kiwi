<?php

namespace Tests\Unit\Entity\Location;

use App\Entity\Activity\Activity;
use App\Entity\Location\Location;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class LocationTest.
 *
 * @covers \App\Entity\Location\Location
 *
 * @group entities
 */
class LocationTest extends KernelTestCase
{
    protected Location $location;

    /** {@inheritdoc} */
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        /* @todo Correctly instantiate tested object to use it. */
        $this->location = new Location();
    }

    /** {@inheritdoc} */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->location);
    }

    public function testGetId(): void
    {
        $expected = '42';
        $property = (new \ReflectionClass(Location::class))
            ->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($this->location, $expected);
        self::assertSame($expected, $this->location->getId());
    }

    public function testSetId(): void
    {
        $expected = '42';
        $property = (new \ReflectionClass(Location::class))
            ->getProperty('id');
        $property->setAccessible(true);
        $this->location->setId($expected);
        self::assertSame($expected, $property->getValue($this->location));
    }

    public function testGetAddress(): void
    {
        $expected = '42';
        $property = (new \ReflectionClass(Location::class))
            ->getProperty('address');
        $property->setAccessible(true);
        $property->setValue($this->location, $expected);
        self::assertSame($expected, $this->location->getAddress());
    }

    public function testSetAddress(): void
    {
        $expected = '42';
        $property = (new \ReflectionClass(Location::class))
            ->getProperty('address');
        $property->setAccessible(true);
        $this->location->setAddress($expected);
        self::assertSame($expected, $property->getValue($this->location));
    }

    public function testGetActivities(): void
    {
        $expected = new ArrayCollection();
        $property = (new \ReflectionClass(Location::class))
            ->getProperty('activities');
        $property->setAccessible(true);
        $property->setValue($this->location, $expected);
        self::assertSame($expected, $this->location->getActivities());
    }

    public function testAddActivity(): void
    {
        // arrange
        /** @var MockObject&Activity */
        $expected = $this->createMock(Activity::class);
        $expected->expects(self::once())->method('setLocation')->with($this->location);

        // act
        $this->location->addActivity($expected);

        // assert
        $property = (new \ReflectionClass(Location::class))
            ->getProperty('activities');
        $property->setAccessible(true);
        $activities = $property->getValue($this->location);
        self::assertInstanceOf(Collection::class, $activities);
        self::assertContains($expected, $activities);
    }

    public function testRemoveActivity(): void
    {
        // arrange
        /** @var MockObject&Activity */
        $expected = $this->createMock(Activity::class);
        $expected->method('getLocation')->willReturn($this->location);
        $expected->expects(self::once())->method('setLocation')->with(null);
        $property = (new \ReflectionClass(Location::class))
            ->getProperty('activities');
        $property->setAccessible(true);
        $activities = $property->getValue($this->location);
        self::assertInstanceOf(Collection::class, $activities);
        $activities->add($expected);

        // act
        $this->location->removeActivity($expected);

        // assert
        $activities = $property->getValue($this->location);
        self::assertInstanceOf(Collection::class, $activities);
        self::assertNotContains($expected, $activities);
    }

    public function testClone(): void
    {
        $this->location->setId('test'); // make sure id has value assigned
        $copy = clone $this->location;

        self::assertNotSame($copy->getId(), $this->location->getId());
    }
}
