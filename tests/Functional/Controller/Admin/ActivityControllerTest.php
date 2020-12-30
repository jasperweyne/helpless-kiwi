<?php

namespace Tests\Functional\Controller\Admin;

use App\Controller\Admin\ActivityController;
use App\Log\EventService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class ActivityControllerTest.
 *
 * @covers \App\Controller\Admin\ActivityController
 */
class ActivityControllerTest extends WebTestCase
{
    /**
     * @var ActivityController
     */
    protected $activityController;

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
        $this->activityController = new ActivityController($this->events);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->activityController);
        unset($this->events);
    }

    public function testIndexAction(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testNewAction(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testShowAction(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testEditAction(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testImageAction(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testDeleteAction(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testPriceNewAction(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testPriceEditAction(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testPresentEditAction(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testSetAmountPresent(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testResetAmountPresent(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }
}
