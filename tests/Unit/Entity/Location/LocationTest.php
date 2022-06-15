<?php

namespace Tests\Unit\Entity\Location;

use App\Entity\Location\Location;
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
}
