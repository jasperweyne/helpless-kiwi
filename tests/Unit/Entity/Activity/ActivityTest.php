<?php

namespace Tests\Unit\Entity\Activity;

use App\Entity\Activity\Activity;
use App\Entity\Group\Group;
use App\Entity\Location\Location;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use ReflectionClass;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Vich\UploaderBundle\Entity\File as EmbeddedFile;

/**
 * Class ActivityTest.
 *
 * @covers \App\Entity\Activity\Activity
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
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testRemoveOption(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
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
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testRemoveRegistration(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
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
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
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
}
