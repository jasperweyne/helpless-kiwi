<?php

namespace Tests\Unit\Log\Doctrine;

use App\Log\Doctrine\EntityEventListener;
use App\Log\EventService;
use App\Reflection\ReflectionService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class EntityEventListenerTest.
 *
 * @covers \App\Log\Doctrine\EntityEventListener
 */
class EntityEventListenerTest extends KernelTestCase
{
    /**
     * @var EntityEventListener
     */
    protected $entityEventListener;

    /**
     * @var EventService
     */
    protected $eventService;

    /**
     * @var ReflectionService
     */
    protected $reflService;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        $this->eventService = self::$container->get(EventService::class);
        $this->reflService = self::$container->get(ReflectionService::class);
        $this->entityEventListener = new EntityEventListener($this->eventService, $this->reflService);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->entityEventListener);
        unset($this->eventService);
        unset($this->reflService);
    }

    public function testOnFlush(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testExtractFields(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }
}
