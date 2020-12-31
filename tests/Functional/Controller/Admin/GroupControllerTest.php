<?php

namespace Tests\Functional\Controller\Admin;

use App\Controller\Admin\GroupController;
use App\Log\EventService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class GroupControllerTest.
 *
 * @covers \App\Controller\Admin\GroupController
 */
class GroupControllerTest extends WebTestCase
{
    /**
     * @var GroupController
     */
    protected $groupController;

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
        $this->groupController = new GroupController($this->events);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->groupController);
        unset($this->events);
    }

    public function testGenerateAction(): void
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

    public function testDeleteAction(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testRelationNewAction(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testRelationAddAction(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testRelationDeleteAction(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }
}
