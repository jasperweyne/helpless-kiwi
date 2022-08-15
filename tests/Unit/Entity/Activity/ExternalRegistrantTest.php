<?php

namespace Tests\Unit\Entity\Activity;

use App\Entity\Activity\Activity;
use App\Entity\Activity\ExternalRegistrant;
use App\Entity\Group\Group;
use ReflectionClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class ExternalRegistrant.
 *
 * @covers \App\Entity\Activity\ExternalRegistrant
 * @group entities
 */
class ExternalRegistrantTest extends KernelTestCase
{
    /**
     * @var ExternalRegistrant
     */
    protected $exteralRegistrant;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        $this->exteralRegistrant = new ExternalRegistrant();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->exteralRegistrant);
    }

    public function testGetEmail(): void
    {
        $expected = 'test@email.kiwi';
        $property = (new ReflectionClass(ExternalRegistrant::class))
            ->getProperty('email');
        $property->setAccessible(true);
        $property->setValue($this->exteralRegistrant, $expected);
        self::assertSame($expected, $this->exteralRegistrant->getEmail());
    }

    public function testSetEmail(): void
    {
        $expected = 'test@email.kiwi';
        $property = (new ReflectionClass(ExternalRegistrant::class))
            ->getProperty('email');
        $property->setAccessible(true);
        $this->exteralRegistrant->setEmail($expected);
        self::assertSame($expected, $property->getValue($this->exteralRegistrant));
    }

    public function testGetName(): void
    {
        $expected = 'Chase';
        $property = (new ReflectionClass(ExternalRegistrant::class))
            ->getProperty('name');
        $property->setAccessible(true);
        $property->setValue($this->exteralRegistrant, $expected);
        self::assertSame($expected, $this->exteralRegistrant->getName());
    }

    public function testSetName(): void
    {
        $expected = 'Chase';
        $property = (new ReflectionClass(ExternalRegistrant::class))
            ->getProperty('name');
        $property->setAccessible(true);
        $this->exteralRegistrant->setName($expected);
        self::assertSame($expected, $property->getValue($this->exteralRegistrant));
    }
}
