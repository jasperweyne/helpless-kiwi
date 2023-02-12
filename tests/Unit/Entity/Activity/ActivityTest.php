<?php

namespace Tests\Unit\Entity\Activity;

use App\Entity\Activity\Activity;
use App\Entity\Activity\PriceOption;
use App\Entity\Activity\Registration;
use App\Entity\Group\Group;
use App\Entity\Location\Location;
use App\Entity\Order;
use App\Entity\Security\LocalAccount;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use ReflectionClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Entity\File as EmbeddedFile;

/**
 * Class ActivityTest.
 *
 * @covers \App\Entity\Activity\Activity
 * @group entities
 */
class ActivityTest extends KernelTestCase
{
    /**
     * @var Activity
     */
    protected $activity;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        $this->activity = new Activity();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->activity);
    }

    public function testGetId(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(Activity::class))
            ->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($this->activity, $expected);
        self::assertSame($expected, $this->activity->getId());
    }

    public function testSetId(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(Activity::class))
            ->getProperty('id');
        $property->setAccessible(true);
        $this->activity->setId($expected);
        self::assertSame($expected, $property->getValue($this->activity));
    }

    public function testGetName(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(Activity::class))
            ->getProperty('name');
        $property->setAccessible(true);
        $property->setValue($this->activity, $expected);
        self::assertSame($expected, $this->activity->getName());
    }

    public function testSetName(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(Activity::class))
            ->getProperty('name');
        $property->setAccessible(true);
        $this->activity->setName($expected);
        self::assertSame($expected, $property->getValue($this->activity));
    }

    public function testGetDescription(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(Activity::class))
            ->getProperty('description');
        $property->setAccessible(true);
        $property->setValue($this->activity, $expected);
        self::assertSame($expected, $this->activity->getDescription());
    }

    public function testSetDescription(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(Activity::class))
            ->getProperty('description');
        $property->setAccessible(true);
        $this->activity->setDescription($expected);
        self::assertSame($expected, $property->getValue($this->activity));
    }

    public function testGetOptions(): void
    {
        $expected = new ArrayCollection();
        $property = (new ReflectionClass(Activity::class))
            ->getProperty('options');
        $property->setAccessible(true);
        $property->setValue($this->activity, $expected);
        self::assertSame($expected, $this->activity->getOptions());
    }

    public function testAddOption(): void
    {
        $expected = new PriceOption();
        $property = (new ReflectionClass(Activity::class))
            ->getProperty('options');
        $property->setAccessible(true);
        $this->activity->addOption($expected);
        self::assertSame($expected, $property->getValue($this->activity)[0]);
    }

    public function testRemoveOption(): void
    {
        $expected = new ArrayCollection();
        $priceOption = new PriceOption();
        $expected->add($priceOption);
        $property = (new ReflectionClass(Activity::class))
            ->getProperty('options');
        $property->setAccessible(true);
        $property->setValue($this->activity, $expected);
        $this->activity->removeOption($priceOption);
        self::assertNotSame($priceOption, $property->getValue($this->activity));
    }

    public function testGetRegistrations(): void
    {
        $expected = new ArrayCollection();
        $property = (new ReflectionClass(Activity::class))
            ->getProperty('registrations');
        $property->setAccessible(true);
        $property->setValue($this->activity, $expected);
        self::assertSame($expected, $this->activity->getRegistrations());
    }

    public function testAddRegistration(): void
    {
        $expected = new Registration();
        $property = (new ReflectionClass(Activity::class))
            ->getProperty('registrations');
        $property->setAccessible(true);
        $this->activity->addRegistration($expected);
        self::assertSame($expected, $property->getValue($this->activity)[0]);
    }

    public function testRemoveRegistration(): void
    {
        $expected = new ArrayCollection();
        $registration = new Registration();
        $expected->add($registration);
        $property = (new ReflectionClass(Activity::class))
            ->getProperty('registrations');
        $property->setAccessible(true);
        $property->setValue($this->activity, $expected);
        $this->activity->removeRegistration($registration);
        self::assertNotSame($registration, $property->getValue($this->activity));
    }

    public function testGetAuthor(): void
    {
        $expected = new Group();
        $property = (new ReflectionClass(Activity::class))
            ->getProperty('author');
        $property->setAccessible(true);
        $property->setValue($this->activity, $expected);
        self::assertSame($expected, $this->activity->getAuthor());
    }

    public function testGetCurrentRegistrations(): void
    {
        $rCurrent = new Registration();
        $rDeleted = (new Registration())->setDeleteDate(new \DateTime());
        $rReserve = (new Registration())->setReservePosition(Order::create('a'));
        $expected = new ArrayCollection([$rCurrent, $rDeleted, $rReserve]);

        $property = (new ReflectionClass(Activity::class))
            ->getProperty('registrations');
        $property->setAccessible(true);
        $property->setValue($this->activity, $expected);

        self::assertTrue($this->activity->getCurrentRegistrations()->contains($rCurrent));
        self::assertFalse($this->activity->getCurrentRegistrations()->contains($rReserve));
        self::assertFalse($this->activity->getCurrentRegistrations()->contains($rDeleted));
    }

    /**
     * @depends testGetCurrentRegistrations
     */
    public function testAddCurrentRegistration(): void
    {
        $registration = new Registration();
        $this->activity->addCurrentRegistration($registration);
        self::assertTrue($this->activity->getCurrentRegistrations()->contains($registration));
    }

    /**
     * @depends testAddCurrentRegistration
     */
    public function testRemoveCurrentRegistration(): void
    {
        $registration = new Registration();
        $this->activity->addCurrentRegistration($registration);

        $this->activity->removeCurrentRegistration($registration);
        self::assertFalse($this->activity->getCurrentRegistrations()->contains($registration));
    }

    public function testGetDeregistrations(): void
    {
        $rCurrent = new Registration();
        $rDeleted = (new Registration())->setDeleteDate(new \DateTime());
        $rReserve = (new Registration())->setReservePosition(Order::create('a'));
        $expected = new ArrayCollection([$rCurrent, $rDeleted, $rReserve]);

        $property = (new ReflectionClass(Activity::class))
            ->getProperty('registrations');
        $property->setAccessible(true);
        $property->setValue($this->activity, $expected);

        $result = $this->activity->getDeregistrations();

        self::assertTrue($result->contains($rDeleted));
        self::assertFalse($result->contains($rReserve));
        self::assertFalse($result->contains($rCurrent));
    }

    /**
     * @depends testGetDeregistrations
     */
    public function testAddDeregistration(): void
    {
        $registration = (new Registration())->setDeleteDate(new \DateTime());
        $this->activity->addDeregistration($registration);
        self::assertTrue($this->activity->getDeregistrations()->contains($registration));
    }

    /**
     * @depends testAddDeregistration
     */
    public function testRemoveDeregistration(): void
    {
        $registration = (new Registration())->setDeleteDate(new \DateTime());
        $this->activity->addDeregistration($registration);

        $this->activity->removeDeregistration($registration);
        self::assertFalse($this->activity->getDeregistrations()->contains($registration));
    }

    public function testGetReserveRegistrations(): void
    {
        $rCurrent = new Registration();
        $rDeleted = (new Registration())->setDeleteDate(new \DateTime());
        $rReserve = (new Registration())->setReservePosition(Order::create('a'));
        $expected = new ArrayCollection([$rCurrent, $rDeleted, $rReserve]);

        $property = (new ReflectionClass(Activity::class))
            ->getProperty('registrations');
        $property->setAccessible(true);
        $property->setValue($this->activity, $expected);

        $result = $this->activity->getReserveRegistrations();

        self::assertTrue($result->contains($rReserve));
        self::assertFalse($result->contains($rDeleted));
        self::assertFalse($result->contains($rCurrent));
    }

    /**
     * @depends testGetReserveRegistrations
     */
    public function testAddReserveRegistration(): void
    {
        $registration = (new Registration())->setReservePosition(Order::create('a'));
        $this->activity->addReserveRegistration($registration);
        self::assertTrue($this->activity->getReserveRegistrations()->contains($registration));
    }

    /**
     * @depends testAddReserveRegistration
     */
    public function testRemoveReserveRegistration(): void
    {
        $registration = (new Registration())->setReservePosition(Order::create('a'));
        $this->activity->addReserveRegistration($registration);

        $this->activity->removeReserveRegistration($registration);
        self::assertFalse($this->activity->getReserveRegistrations()->contains($registration));
    }

    public function testSetAuthor(): void
    {
        $expected = new Group();
        $property = (new ReflectionClass(Activity::class))
            ->getProperty('author');
        $property->setAccessible(true);
        $this->activity->setAuthor($expected);
        self::assertSame($expected, $property->getValue($this->activity));
    }

    public function testGetTarget(): void
    {
        $expected = new Group();
        $property = (new ReflectionClass(Activity::class))
            ->getProperty('target');
        $property->setAccessible(true);
        $property->setValue($this->activity, $expected);
        self::assertSame($expected, $this->activity->getTarget());
    }

    public function testSetTarget(): void
    {
        $expected = new Group();
        $property = (new ReflectionClass(Activity::class))
            ->getProperty('target');
        $property->setAccessible(true);
        $this->activity->setTarget($expected);
        self::assertSame($expected, $property->getValue($this->activity));
    }

    public function testGetColor(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(Activity::class))
            ->getProperty('color');
        $property->setAccessible(true);
        $property->setValue($this->activity, $expected);
        self::assertSame($expected, $this->activity->getColor());
    }

    public function testSetColor(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(Activity::class))
            ->getProperty('color');
        $property->setAccessible(true);
        $this->activity->setColor($expected);
        self::assertSame($expected, $property->getValue($this->activity));
    }

    public function testGetStart(): void
    {
        $expected = new DateTime();
        $property = (new ReflectionClass(Activity::class))
            ->getProperty('start');
        $property->setAccessible(true);
        $property->setValue($this->activity, $expected);
        self::assertSame($expected, $this->activity->getStart());
    }

    public function testSetStart(): void
    {
        $expected = new DateTime();
        $property = (new ReflectionClass(Activity::class))
            ->getProperty('start');
        $property->setAccessible(true);
        $this->activity->setStart($expected);
        self::assertSame($expected, $property->getValue($this->activity));
    }

    public function testGetEnd(): void
    {
        $expected = new DateTime();
        $property = (new ReflectionClass(Activity::class))
            ->getProperty('end');
        $property->setAccessible(true);
        $property->setValue($this->activity, $expected);
        self::assertSame($expected, $this->activity->getEnd());
    }

    public function testSetEnd(): void
    {
        $expected = new DateTime();
        $property = (new ReflectionClass(Activity::class))
            ->getProperty('end');
        $property->setAccessible(true);
        $this->activity->setEnd($expected);
        self::assertSame($expected, $property->getValue($this->activity));
    }

    public function testGetDeadline(): void
    {
        $expected = new DateTime();
        $property = (new ReflectionClass(Activity::class))
            ->getProperty('deadline');
        $property->setAccessible(true);
        $property->setValue($this->activity, $expected);
        self::assertSame($expected, $this->activity->getDeadline());
    }

    public function testSetDeadline(): void
    {
        $expected = new DateTime();
        $property = (new ReflectionClass(Activity::class))
            ->getProperty('deadline');
        $property->setAccessible(true);
        $this->activity->setDeadline($expected);
        self::assertSame($expected, $property->getValue($this->activity));
    }

    public function testGetLocation(): void
    {
        $expected = new Location();
        $property = (new ReflectionClass(Activity::class))
            ->getProperty('location');
        $property->setAccessible(true);
        $property->setValue($this->activity, $expected);
        self::assertSame($expected, $this->activity->getLocation());
    }

    public function testSetLocation(): void
    {
        $expected = new Location();
        $property = (new ReflectionClass(Activity::class))
            ->getProperty('location');
        $property->setAccessible(true);
        $this->activity->setLocation($expected);
        self::assertSame($expected, $property->getValue($this->activity));
    }

    public function testSetImageFile(): void
    {
        $expected = new File(__DIR__, false);
        $property = (new ReflectionClass(Activity::class))
            ->getProperty('imageFile');
        $property->setAccessible(true);
        $this->activity->setImageFile($expected);
        self::assertSame($expected, $property->getValue($this->activity));
    }

    public function testGetImageFile(): void
    {
        $expected = new File(__DIR__, false);
        $property = (new ReflectionClass(Activity::class))
            ->getProperty('imageFile');
        $property->setAccessible(true);
        $property->setValue($this->activity, $expected);
        self::assertSame($expected, $this->activity->getImageFile());
    }

    public function testGetImageUpdatedAt(): void
    {
        $expected = new DateTime();
        $property = (new ReflectionClass(Activity::class))
            ->getProperty('imageUpdatedAt');
        $property->setAccessible(true);
        $property->setValue($this->activity, $expected);
        self::assertSame($expected, $this->activity->getImageUpdatedAt());
    }

    public function testSetImageUpdatedAt(): void
    {
        $expected = new DateTime();
        $property = (new ReflectionClass(Activity::class))
            ->getProperty('imageUpdatedAt');
        $property->setAccessible(true);
        $this->activity->setImageUpdatedAt($expected);
        self::assertSame($expected, $property->getValue($this->activity));
    }

    public function testSetImage(): void
    {
        $expected = new EmbeddedFile();
        $property = (new ReflectionClass(Activity::class))
            ->getProperty('image');
        $property->setAccessible(true);
        $this->activity->setImage($expected);
        self::assertSame($expected, $property->getValue($this->activity));
    }

    public function testGetImage(): void
    {
        $expected = new EmbeddedFile();
        $property = (new ReflectionClass(Activity::class))
            ->getProperty('image');
        $property->setAccessible(true);
        $property->setValue($this->activity, $expected);
        self::assertSame($expected, $this->activity->getImage());
    }

    public function testHasCapacity(): void
    {
        $capacity = 42;
        $property = (new ReflectionClass(Activity::class))
            ->getProperty('capacity');
        $property->setAccessible(true);
        $property->setValue($this->activity, $capacity);
        self::assertTrue($this->activity->hasCapacity());
    }

    public function testHasCapacityWhileEmpty(): void
    {
        self::assertFalse($this->activity->hasCapacity());
    }

    public function testGetCapacity(): void
    {
        $expected = 42;
        $property = (new ReflectionClass(Activity::class))
            ->getProperty('capacity');
        $property->setAccessible(true);
        $property->setValue($this->activity, $expected);
        self::assertSame($expected, $this->activity->getCapacity());
    }

    public function testSetCapacity(): void
    {
        $expected = 42;
        $property = (new ReflectionClass(Activity::class))
            ->getProperty('capacity');
        $property->setAccessible(true);
        $this->activity->setCapacity($expected);
        self::assertSame($expected, $property->getValue($this->activity));
    }

    public function testAtCapacity(): void
    {
        /* @todo This test is incomplete. */
        self::markTestIncomplete();
    }

    public function testGetPresent(): void
    {
        $property = (new ReflectionClass(Activity::class))
            ->getProperty('present');
        $property->setAccessible(true);
        $property->setValue($this->activity, null);
        self::assertNull($this->activity->getPresent());
    }

    public function testSetPresent(): void
    {
        $expected = 42;
        $property = (new ReflectionClass(Activity::class))
            ->getProperty('present');
        $property->setAccessible(true);
        $this->activity->setPresent($expected);
        self::assertSame($expected, $property->getValue($this->activity));
    }

    public function testGetVisibleAfter(): void
    {
        $expected = new \DateTime();
        $property = (new ReflectionClass(Activity::class))
            ->getProperty('visibleAfter');
        $property->setAccessible(true);
        $property->setValue($this->activity, $expected);
        self::assertSame($expected, $this->activity->getVisibleAfter());
    }

    public function testSetVisibleAfter(): void
    {
        $expected = new \DateTime();
        $property = (new ReflectionClass(Activity::class))
            ->getProperty('visibleAfter');
        $property->setAccessible(true);
        $this->activity->setVisibleAfter($expected);
        self::assertSame($expected, $property->getValue($this->activity));
    }

    public function testIsVisible(): void
    {
        $expected = $this->createMock(Group::class);
        $target = (new ReflectionClass(Activity::class))
            ->getProperty('target');
        $target->setAccessible(true);
        $target->setValue($this->activity, $expected);
        $end = (new ReflectionClass(Activity::class))
            ->getProperty('end');
        $end->setAccessible(true);
        $end->setValue($this->activity, new \DateTime('tomorrow'));
        $visibleAfter = (new ReflectionClass(Activity::class))
            ->getProperty('visibleAfter');
        $visibleAfter->setAccessible(true);
        $visibleAfter->setValue($this->activity, new \DateTime('yesterday'));
        self::assertTrue($this->activity->isVisible([$expected]));
    }

    public function testIsVisibleBy(): void
    {
        // todo: update dependencies, as phpunit 7.5 doesn't support mocks when strict
        $group = new Group();
        $value = (new LocalAccount())->addRelation($group);

        $target = (new ReflectionClass(Activity::class))
            ->getProperty('target');
        $target->setAccessible(true);
        $target->setValue($this->activity, $group);
        $end = (new ReflectionClass(Activity::class))
            ->getProperty('end');
        $end->setAccessible(true);
        $end->setValue($this->activity, new \DateTime('tomorrow'));
        $visibleAfter = (new ReflectionClass(Activity::class))
            ->getProperty('visibleAfter');
        $visibleAfter->setAccessible(true);
        $visibleAfter->setValue($this->activity, new \DateTime('yesterday'));
        self::assertTrue($this->activity->isVisibleBy($value));
    }
}
