<?php

namespace Tests\Functional\Controller\Admin;

use App\Controller\Admin\SecurityController;
use App\Log\EventService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class SecurityControllerTest.
 *
 * @covers \App\Controller\Admin\SecurityController
 */
class SecurityControllerTest extends WebTestCase
{
    /**
     * @var SecurityController
     */
    protected $securityController;

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
        $this->securityController = new SecurityController($this->events);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->securityController);
        unset($this->events);
    }

    public function testGetMenuItems(): void
    {
        /* @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testIndexAction(): void
    {
        /* @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testNewAction(): void
    {
        /* @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testShowAction(): void
    {
        /* @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testEditAction(): void
    {
        /* @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testDeleteAction(): void
    {
        /* @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testRolesAction(): void
    {
        /* @todo This test is incomplete. */
        $this->markTestIncomplete();
    }
}
