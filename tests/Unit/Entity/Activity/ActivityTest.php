<?php

namespace Tests\Unit\Entity\Activity;

use App\Entity\Activity\Activity;
use App\Entity\Activity\PriceOption;
use App\Entity\Activity\Registration;
use App\Entity\Group\Group;
use App\Entity\Group\Relation;
use App\Entity\Location\Location;
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
        $this->assertSame($expected, $this->activity->getId());
    }

    public function testSetId(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(Activity::class))
            ->getProperty('id');
        $property->setAccessible(true);
        $this->activity->setId($expected);
        $this->assertSame($expected, $property->getValue($this->activity));
    }

    public function testGetName(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(Activity::class))
            ->getProperty('name');
        $property->setAccessible(true);
        $property->setValue($this->activity, $expected);
        $this->assertSame($expected, $this->activity->getName());
    }

    public function testSetName(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(Activity::class))
            ->getProperty('name');
        $property->setAccessible(true);
        $this->activity->setName($expected);
        $this->assertSame($expected, $property->getValue($this->activity));
    }

    public function testGetDescription(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(Activity::class))
            ->getProperty('description');
        $property->setAccessible(true);
        $property->setValue($this->activity, $expected);
        $this->assertSame($expected, $this->activity->getDescription());
    }

    public function testSetDescription(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(Activity::class))
            ->getProperty('description');
        $property->setAccessible(true);
        $this->activity->setDescription($expected);
        $this->assertSame($expected, $property->getValue($this->activity));
    }

    public function testGetOptions(): void
    {
        $expected = new ArrayCollection();
        $property = (new ReflectionClass(Activity::class))
            ->getProperty('options');
        $property->setAccessible(true);
        $property->setValue($this->activity, $expected);
        $this->assertSame($expected, $this->activity->getOptions());
    }

    public function testAddOption(): void
    {
        $expected = new PriceOption();
        $property = (new ReflectionClass(Activity::class))
            ->getProperty('options');
        $property->setAccessible(true);
        $this->activity->addOption($expected);
        $this->assertSame($expected, $property->getValue($this->activity)[0]);
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
        $this->assertNotSame($priceOption, $property->getValue($this->activity));
    }

    public function testGetRegistrations(): void
    {
        $expected = new ArrayCollection();
        $property = (new ReflectionClass(Activity::class))
            ->getProperty('registrations');
        $property->setAccessible(true);
        $property->setValue($this->activity, $expected);
        $this->assertSame($expected, $this->activity->getRegistrations());
    }

    public function testAddRegistration(): void
    {
        $expected = new Registration();
        $property = (new ReflectionClass(Activity::class))
            ->getProperty('registrations');
        $property->setAccessible(true);
        $this->activity->addRegistration($expected);
        $this->assertSame($expected, $property->getValue($this->activity)[0]);
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
        $this->assertNotSame($registration, $property->getValue($this->activity));
    }

    public function testGetAuthor(): void
    {
        $expected = new Group();
        $property = (new ReflectionClass(Activity::class))
            ->getProperty('author');
        $property->setAccessible(true);
        $property->setValue($this->activity, $expected);
        $this->assertSame($expected, $this->activity->getAuthor());
    }

    public function testSetAuthor(): void
    {
        $expected = new Group();
        $property = (new ReflectionClass(Activity::class))
            ->getProperty('author');
        $property->setAccessible(true);
        $this->activity->setAuthor($expected);
        $this->assertSame($expected, $property->getValue($this->activity));
    }

    public function testGetTarget(): void
    {
        $expected = new Group();
        $property = (new ReflectionClass(Activity::class))
            ->getProperty('target');
        $property->setAccessible(true);
        $property->setValue($this->activity, $expected);
        $this->assertSame($expected, $this->activity->getTarget());
    }

    public function testSetTarget(): void
    {
        $expected = new Group();
        $property = (new ReflectionClass(Activity::class))
            ->getProperty('target');
        $property->setAccessible(true);
        $this->activity->setTarget($expected);
        $this->assertSame($expected, $property->getValue($this->activity));
    }

    public function testGetColor(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(Activity::class))
            ->getProperty('color');
        $property->setAccessible(true);
        $property->setValue($this->activity, $expected);
        $this->assertSame($expected, $this->activity->getColor());
    }

    public function testSetColor(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(Activity::class))
            ->getProperty('color');
        $property->setAccessible(true);
        $this->activity->setColor($expected);
        $this->assertSame($expected, $property->getValue($this->activity));
    }

    public function testGetStart(): void
    {
        $expected = new DateTime();
        $property = (new ReflectionClass(Activity::class))
            ->getProperty('start');
        $property->setAccessible(true);
        $property->setValue($this->activity, $expected);
        $this->assertSame($expected, $this->activity->getStart());
    }

    public function testSetStart(): void
    {
        $expected = new DateTime();
        $property = (new ReflectionClass(Activity::class))
            ->getProperty('start');
        $property->setAccessible(true);
        $this->activity->setStart($expected);
        $this->assertSame($expected, $property->getValue($this->activity));
    }

    public function testGetEnd(): void
    {
        $expected = new DateTime();
        $property = (new ReflectionClass(Activity::class))
            ->getProperty('end');
        $property->setAccessible(true);
        $property->setValue($this->activity, $expected);
        $this->assertSame($expected, $this->activity->getEnd());
    }

    public function testSetEnd(): void
    {
        $expected = new DateTime();
        $property = (new ReflectionClass(Activity::class))
            ->getProperty('end');
        $property->setAccessible(true);
        $this->activity->setEnd($expected);
        $this->assertSame($expected, $property->getValue($this->activity));
    }

    public function testGetDeadline(): void
    {
        $expected = new DateTime();
        $property = (new ReflectionClass(Activity::class))
            ->getProperty('deadline');
        $property->setAccessible(true);
        $property->setValue($this->activity, $expected);
        $this->assertSame($expected, $this->activity->getDeadline());
    }

    public function testSetDeadline(): void
    {
        $expected = new DateTime();
        $property = (new ReflectionClass(Activity::class))
            ->getProperty('deadline');
        $property->setAccessible(true);
        $this->activity->setDeadline($expected);
        $this->assertSame($expected, $property->getValue($this->activity));
    }

    public function testGetLocation(): void
    {
        $expected = new Location();
        $property = (new ReflectionClass(Activity::class))
            ->getProperty('location');
        $property->setAccessible(true);
        $property->setValue($this->activity, $expected);
        $this->assertSame($expected, $this->activity->getLocation());
    }

    public function testSetLocation(): void
    {
        $expected = new Location();
        $property = (new ReflectionClass(Activity::class))
            ->getProperty('location');
        $property->setAccessible(true);
        $this->activity->setLocation($expected);
        $this->assertSame($expected, $property->getValue($this->activity));
    }

    public function testSetImageFile(): void
    {
        $expected = new File(__DIR__, false);
        $property = (new ReflectionClass(Activity::class))
            ->getProperty('imageFile');
        $property->setAccessible(true);
        $this->activity->setImageFile($expected);
        $this->assertSame($expected, $property->getValue($this->activity));
    }

    public function testGetImageFile(): void
    {
        $expected = new File(__DIR__, false);
        $property = (new ReflectionClass(Activity::class))
            ->getProperty('imageFile');
        $property->setAccessible(true);
        $property->setValue($this->activity, $expected);
        $this->assertSame($expected, $this->activity->getImageFile());
    }

    public function testSetImage(): void
    {
        $expected = new EmbeddedFile();
        $property = (new ReflectionClass(Activity::class))
            ->getProperty('image');
        $property->setAccessible(true);
        $this->activity->setImage($expected);
        $this->assertSame($expected, $property->getValue($this->activity));
    }

    public function testGetImage(): void
    {
        $expected = new EmbeddedFile();
        $property = (new ReflectionClass(Activity::class))
            ->getProperty('image');
        $property->setAccessible(true);
        $property->setValue($this->activity, $expected);
        $this->assertSame($expected, $this->activity->getImage());
    }

    public function testHasCapacity(): void
    {
        $expected = true;
        $capacity = 42;
        $property = (new ReflectionClass(Activity::class))
            ->getProperty('capacity');
        $property->setAccessible(true);
        $property->setValue($this->activity, $capacity);
        $this->assertSame($expected, $this->activity->hasCapacity());
    }

    public function testHasCapacityWhileEmpty(): void
    {
        $expected = false;
        $this->assertSame($expected, $this->activity->hasCapacity());
    }

    public function testGetCapacity(): void
    {
        $expected = 42;
        $property = (new ReflectionClass(Activity::class))
            ->getProperty('capacity');
        $property->setAccessible(true);
        $property->setValue($this->activity, $expected);
        $this->assertSame($expected, $this->activity->getCapacity());
    }

    public function testSetCapacity(): void
    {
        $expected = 42;
        $property = (new ReflectionClass(Activity::class))
            ->getProperty('capacity');
        $property->setAccessible(true);
        $this->activity->setCapacity($expected);
        $this->assertSame($expected, $property->getValue($this->activity));
    }

    public function testGetPresent(): void
    {
        $expected = null;
        $property = (new ReflectionClass(Activity::class))
            ->getProperty('present');
        $property->setAccessible(true);
        $property->setValue($this->activity, $expected);
        $this->assertSame($expected, $this->activity->getPresent());
    }

    public function testSetPresent(): void
    {
        $expected = 42;
        $property = (new ReflectionClass(Activity::class))
            ->getProperty('present');
        $property->setAccessible(true);
        $this->activity->setPresent($expected);
        $this->assertSame($expected, $property->getValue($this->activity));
    }

    public function testGetVisibleAfter(): void
    {
        $expected = new \DateTime();
        $property = (new ReflectionClass(Activity::class))
            ->getProperty('visibleAfter');
        $property->setAccessible(true);
        $property->setValue($this->activity, $expected);
        $this->assertSame($expected, $this->activity->getVisibleAfter());
    }

    public function testSetVisibleAfter(): void
    {
        $expected = new \DateTime();
        $property = (new ReflectionClass(Activity::class))
            ->getProperty('visibleAfter');
        $property->setAccessible(true);
        $this->activity->setVisibleAfter($expected);
        $this->assertSame($expected, $property->getValue($this->activity));
    }

    public function testIsVisible(): void
    {
        /** @var MockObject&Group */
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
        $this->assertTrue($this->activity->isVisible([$expected]));
    }

    public function testIsVisibleBy(): void
    {
        // todo: update dependencies, as phpunit 7.5 doesn't support mocks when strict
        $group = new Group();
        $relation = (new Relation())->setGroup($group);
        $value = (new LocalAccount())->addRelation($relation);

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
        $this->assertTrue($this->activity->isVisibleBy($value));
    }
}
