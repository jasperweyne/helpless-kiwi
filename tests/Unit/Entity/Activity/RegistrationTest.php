<?php

namespace Tests\Unit\Entity\Activity;

use App\Entity\Activity\Activity;
use App\Entity\Activity\PriceOption;
use App\Entity\Activity\Registration;
use DateTime;
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

    public function testGetPersonId(): void
    {
        $expected = '43';
        $property = (new ReflectionClass(Registration::class))
            ->getProperty('person_id');
        $property->setAccessible(true);
        $property->setValue($this->registration, $expected);
        $this->assertSame($expected, $this->registration->getPersonId());
    }

    public function testSetPersonId(): void
    {
        $expected = '43';
        $property = (new ReflectionClass(Registration::class))
            ->getProperty('person_id');
        $property->setAccessible(true);
        $this->registration->setPersonId($expected);
        $this->assertSame($expected, $property->getValue($this->registration));
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
        $expected = null;
        $property = (new ReflectionClass(Registration::class))
            ->getProperty('reserve_position');
        $property->setAccessible(true);
        $property->setValue($this->registration, $expected);
        $this->assertSame($expected, $this->registration->getReservePosition());
    }

    public function testGetReservePosition(): void
    {
        $expected = null;
        $property = (new ReflectionClass(Registration::class))
            ->getProperty('reserve_position');
        $property->setAccessible(true);
        $property->setValue($this->registration, $expected);
        $this->assertSame($expected, $this->registration->getReservePosition());
    }

    public function testSetReservePosition(): void
    {
        $expected = null;
        $property = (new ReflectionClass(Registration::class))
            ->getProperty('reserve_position');
        $property->setAccessible(true);
        $this->registration->setReservePosition($expected);
        $this->assertSame($expected, $property->getValue($this->registration));
    }

    public function testGetNewDate(): void
    {
        $expected = new DateTime();
        $property = (new ReflectionClass(Registration::class))
            ->getProperty('newdate');
        $property->setAccessible(true);
        $property->setValue($this->registration, $expected);
        $this->assertSame($expected, $this->registration->getNewDate());
    }

    public function testSetNewDate(): void
    {
        $expected = new DateTime();
        $property = (new ReflectionClass(Registration::class))
            ->getProperty('newdate');
        $property->setAccessible(true);
        $this->registration->setNewDate($expected);
        $this->assertSame($expected, $property->getValue($this->registration));
    }

    public function testGetDeleteDate(): void
    {
        $expected = new DateTime();
        $property = (new ReflectionClass(Registration::class))
            ->getProperty('deletedate');
        $property->setAccessible(true);
        $property->setValue($this->registration, $expected);
        $this->assertSame($expected, $this->registration->getDeleteDate());
    }

    public function testSetDeleteDate(): void
    {
        $expected = new DateTime();
        $property = (new ReflectionClass(Registration::class))
            ->getProperty('deletedate');
        $property->setAccessible(true);
        $this->registration->setDeleteDate($expected);
        $this->assertSame($expected, $property->getValue($this->registration));
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

    public function testGetComment()
    {
        $expected = 'TestComment';
        $property = (new ReflectionClass(Registration::class))
            ->getProperty('comment');
        $property->setAccessible(true);
        $property->setValue($this->registration, $expected);
        $this->assertSame($expected, $this->registration->getComment());
    }

    public function testSetComment()
    {
        $expected = 'TestComment';
        $property = (new ReflectionClass(Registration::class))
            ->getProperty('comment');
        $property->setAccessible(true);
        $this->registration->setComment($expected);
        $this->assertSame($expected, $property->getValue($this->registration));
    }
}
