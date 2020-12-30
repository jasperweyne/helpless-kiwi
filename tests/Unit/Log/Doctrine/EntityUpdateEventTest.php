<?php

namespace Tests\Unit\Log\Doctrine;

use App\Log\Doctrine\EntityUpdateEvent;
use ReflectionClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class EntityUpdateEventTest.
 *
 * @covers \App\Log\Doctrine\EntityUpdateEvent
 */
class EntityUpdateEventTest extends KernelTestCase
{
    /**
     * @var EntityUpdateEvent
     */
    protected $entityUpdateEvent;

    /**
     * @var mixed
     */
    protected $entity;

    /**
     * @var mixed
     */
    protected $oldObject;

    /**
     * @var mixed
     */
    protected $newValues;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        $this->entity = new \stdClass();
        $this->oldObject = [];
        $this->newValues = [];
        $this->entityUpdateEvent = new EntityUpdateEvent($this->entity, $this->oldObject, $this->newValues);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->entityUpdateEvent);
        unset($this->entity);
        unset($this->oldObject);
        unset($this->newValues);
    }

    public function testGetOld(): void
    {
        $expected = [];
        $property = (new ReflectionClass(EntityUpdateEvent::class))
            ->getProperty('old');
        $property->setAccessible(true);
        $property->setValue($this->entityUpdateEvent, $expected);
        $this->assertSame($expected, $this->entityUpdateEvent->getOld());
    }

    public function testGetNew(): void
    {
        $expected = [];
        $property = (new ReflectionClass(EntityUpdateEvent::class))
            ->getProperty('new');
        $property->setAccessible(true);
        $property->setValue($this->entityUpdateEvent, $expected);
        $this->assertSame($expected, $this->entityUpdateEvent->getNew());
    }

    public function testGetOldChanged(): void
    {
        /* @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testGetNewChanged(): void
    {
        /* @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testGetTitle(): void
    {
        /* @todo This test is incomplete. */
        $this->markTestIncomplete();
    }
}
