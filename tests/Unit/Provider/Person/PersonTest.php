<?php

namespace Tests\Unit\Provider\Person;

use App\Provider\Person\Person;
use ReflectionClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class PersonTest.
 *
 * @covers \App\Provider\Person\Person
 */
class PersonTest extends KernelTestCase
{
    /**
     * @var Person
     */
    protected $person;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        /* @todo Correctly instantiate tested object to use it. */
        $this->person = new Person();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->person);
    }

    public function testGetId(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(Person::class))
            ->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($this->person, $expected);
        $this->assertSame($expected, $this->person->getId());
    }

    public function testSetId(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(Person::class))
            ->getProperty('id');
        $property->setAccessible(true);
        $this->person->setId($expected);
        $this->assertSame($expected, $property->getValue($this->person));
    }

    public function testGetEmail(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(Person::class))
            ->getProperty('email');
        $property->setAccessible(true);
        $property->setValue($this->person, $expected);
        $this->assertSame($expected, $this->person->getEmail());
    }

    public function testSetEmail(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(Person::class))
            ->getProperty('email');
        $property->setAccessible(true);
        $this->person->setEmail($expected);
        $this->assertSame($expected, $property->getValue($this->person));
    }

    public function testGetFields(): void
    {
        $expected = [];
        $property = (new ReflectionClass(Person::class))
            ->getProperty('fields');
        $property->setAccessible(true);
        $property->setValue($this->person, $expected);
        $this->assertSame($expected, $this->person->getFields());
    }

    public function testSetFields(): void
    {
        $expected = [];
        $property = (new ReflectionClass(Person::class))
            ->getProperty('fields');
        $property->setAccessible(true);
        $this->person->setFields($expected);
        $this->assertSame($expected, $property->getValue($this->person));
    }

    public function testGetName(): void
    {
        /* @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testGetShortname(): void
    {
        /* @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testGetCanonical(): void
    {
        /* @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function test__toString(): void
    {
        /* @todo This test is incomplete. */
        $this->markTestIncomplete();
    }
}
