<?php

namespace Tests\Unit\Log\Doctrine;

use App\Log\Doctrine\EntityEventListener;
use App\Log\EventService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class EntityEventListenerTest.
 *
 * @covers \App\Log\Doctrine\EntityEventListener
 */
class EntityEventListenerTest extends KernelTestCase
{
    protected EntityEventListener $entityEventListener;

    protected EventService $eventService;

    /** {@inheritdoc} */
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        $this->eventService = self::getContainer()->get(EventService::class);
        $this->entityEventListener = new EntityEventListener($this->eventService);
    }

    /** {@inheritdoc} */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->entityEventListener);
        unset($this->eventService);
    }

    public function testOnFlush(): void
    {
        /* @todo This test is incomplete. */
        self::markTestIncomplete();
    }

    public function testExtractFields(): void
    {
        /* @todo This test is incomplete. */
        self::markTestIncomplete();
    }
}
