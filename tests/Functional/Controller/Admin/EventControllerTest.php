<?php

namespace Tests\Functional\Controller\Admin;

use App\Controller\Admin\EventController;
use App\Log\EventService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class EventControllerTest.
 *
 * @covers \App\Controller\Admin\EventController
 */
class EventControllerTest extends WebTestCase
{
    /**
     * @var EventController
     */
    protected $eventController;

    /**
     * @var EventService
     */
    protected $events;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        $this->events = self::$container->get(EventService::class);
        $this->eventController = new EventController($this->events);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->eventController);
        unset($this->events);
    }

    public function testIndexAction(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }
}
