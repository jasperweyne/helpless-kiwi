<?php

namespace Tests\Unit\Log\Doctrine;

use App\Log\Doctrine\EntityNewEvent;
use ReflectionClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class EntityNewEventTest.
 *
 * @covers \App\Log\Doctrine\EntityNewEvent
 */
class EntityNewEventTest extends KernelTestCase
{
    /**
     * @var EntityNewEvent
     */
    protected $entityNewEvent;

    /**
     * @var mixed
     */
    protected $entity;

    /**
     * @var mixed
     */
    protected $fields;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        $this->entity = new \stdClass();
        $this->fields = new \stdClass();
        $this->entityNewEvent = new EntityNewEvent($this->entity, $this->fields);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->entityNewEvent);
        unset($this->entity);
        unset($this->fields);
    }

    public function testGetFields(): void
    {
        $expected = [];
        $property = (new ReflectionClass(EntityNewEvent::class))
            ->getProperty('fields');
        $property->setAccessible(true);
        $property->setValue($this->entityNewEvent, $expected);
        self::assertSame($expected, $this->entityNewEvent->getFields());
    }

    public function testGetTitle(): void
    {
        /* @todo This test is incomplete. */
        self::markTestIncomplete();
    }
}
