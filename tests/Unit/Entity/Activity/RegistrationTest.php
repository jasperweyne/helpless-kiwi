<?php

namespace Tests\Unit\Entity\Activity;

use App\Entity\Activity\Activity;
use App\Entity\Activity\PriceOption;
use App\Entity\Activity\Registration;
use ReflectionClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class RegistrationTest.
 *
 * @covers \App\Entity\Activity\Registration
 */
class RegistrationTest extends KernelTestCase
{
    /**
     * @var Registration
     */
    protected $registration;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        /* @todo Correctly instantiate tested object to use it. */
        $this->registration = new Registration();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->registration);
    }

    public function testGetId(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(Registration::class))
            ->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($this->registration, $expected);
        $this->assertSame($expected, $this->registration->getId());
    }

    public function testSetId(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(Registration::class))
            ->getProperty('id');
        $property->setAccessible(true);
        $this->registration->setId($expected);
        $this->assertSame($expected, $property->getValue($this->registration));
    }

    public function testGetOption(): void
    {
        $expected = new PriceOption();
        $property = (new ReflectionClass(Registration::class))
            ->getProperty('option');
        $property->setAccessible(true);
        $property->setValue($this->registration, $expected);
        $this->assertSame($expected, $this->registration->getOption());
    }

    public function testSetOption(): void
    {
        $expected = new PriceOption();
        $property = (new ReflectionClass(Registration::class))
            ->getProperty('option');
        $property->setAccessible(true);
        $this->registration->setOption($expected);
        $this->assertSame($expected, $property->getValue($this->registration));
    }

    public function testGetPerson(): void
    {
        /* @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testSetPerson(): void
    {
        /* @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testGetActivity(): void
    {
        $expected = new Activity();
        $property = (new ReflectionClass(Registration::class))
            ->getProperty('activity');
        $property->setAccessible(true);
        $property->setValue($this->registration, $expected);
        $this->assertSame($expected, $this->registration->getActivity());
    }

    public function testSetActivity(): void
    {
        $expected = new Activity();
        $property = (new ReflectionClass(Registration::class))
            ->getProperty('activity');
        $property->setAccessible(true);
        $this->registration->setActivity($expected);
        $this->assertSame($expected, $property->getValue($this->registration));
    }

    public function testIsReserve(): void
    {
        /* @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testGetReservePosition(): void
    {
        /* @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testSetReservePosition(): void
    {
        /* @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testGetNewDate(): void
    {
        /* @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testSetNewDate(): void
    {
        /* @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testGetDeleteDate(): void
    {
        /* @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testSetDeleteDate(): void
    {
        /* @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testGetPresent(): void
    {
        $expected = null;
        $property = (new ReflectionClass(Registration::class))
            ->getProperty('present');
        $property->setAccessible(true);
        $property->setValue($this->registration, $expected);
        $this->assertSame($expected, $this->registration->getPresent());
    }

    public function testSetPresent(): void
    {
        $expected = null;
        $property = (new ReflectionClass(Registration::class))
            ->getProperty('present');
        $property->setAccessible(true);
        $this->registration->setPresent($expected);
        $this->assertSame($expected, $property->getValue($this->registration));
    }
}
