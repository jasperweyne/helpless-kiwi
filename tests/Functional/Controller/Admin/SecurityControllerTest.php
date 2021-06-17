<?php

namespace Tests\Functional\Controller\Admin;

use App\Controller\Admin\SecurityController;
use App\Log\EventService;
use App\Tests\AuthWebTestCase;

/**
 * Class SecurityControllerTest.
 *
 * @covers \App\Controller\Admin\SecurityController
 */
class SecurityControllerTest extends AuthWebTestCase
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
        $this->login();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();
    }

    public function testGetMenuItems(): void
    {
        /* @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testIndexAction(): void
    {
        // Act
        $this->client->request('GET', '/admin/security/');

        // Assert
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testNewAction(): void
    {
        // Act
        $crawler = $this->client->request('GET', '/admin/security/new');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $form = $crawler->selectButton('Toevoegen')->form();
        $form['local_account[name]'] = 'John';
        $form['local_account[email]'] = 'john@doe.eyes';
        $crawler = $this->client->submit($form);

        // Assert
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        // TODO: figure our if this is actually the way to test this....
        $this->assertEquals(1, $crawler->filter('.top > h3:nth-child(1)')->count());
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
