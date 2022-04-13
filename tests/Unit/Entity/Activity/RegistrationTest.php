<?php

namespace Tests\Unit\Entity\Activity;

use App\Entity\Activity\Activity;
use App\Entity\Activity\PriceOption;
use App\Entity\Activity\Registration;
use App\Entity\Order;
use App\Entity\Security\LocalAccount;
use App\Repository\RegistrationRepository;
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

    public function testGetPerson(): void
    {
        $expected = new LocalAccount();
        $expected->setEmail('john@doe.eyes');
        $property = (new ReflectionClass(Registration::class))
            ->getProperty('person');
        $property->setAccessible(true);
        $property->setValue($this->registration, $expected);
        $this->assertSame($expected, $this->registration->getPerson());
    }

    public function testSetPerson(): void
    {
        $expected = new LocalAccount();
        $expected->setEmail('john@doe.eyes');
        $property = (new ReflectionClass(Registration::class))
            ->getProperty('person');
        $property->setAccessible(true);
        $this->registration->setPerson($expected);
        $this->assertSame($expected, $this->registration->getPerson());
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

    public function testIsReserveTrue(): void
    {
        $expected = true;
        $property = (new ReflectionClass(Registration::class))
            ->getProperty('reserve_position');
        $property->setAccessible(true);
        $reserveOrder = Order::create(RegistrationRepository::MINORDER());
        $this->registration->setReservePosition($reserveOrder);
        $this->assertSame($expected, $this->registration->isReserve());
    }

    public function testIsReserveFalse(): void
    {
        $expected = false;
        $this->assertSame($expected, $this->registration->isReserve());
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
        $expected = 'aaaaaaaaaaaaaaaa';
        $property = (new ReflectionClass(Registration::class))
            ->getProperty('reserve_position');
        $property->setAccessible(true);
        $counter = Order::create(RegistrationRepository::MINORDER());
        $this->registration->setReservePosition($counter);
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
