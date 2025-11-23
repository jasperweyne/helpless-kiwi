<?php

namespace Tests\Functional\Controller\Admin;

use App\Controller\Admin\AdminController;
use App\Tests\AuthWebTestCase;

/**
 * Class AdminControllerTest.
 *
 * @covers \App\Controller\Admin\AdminController
 */
class AdminControllerTest extends AuthWebTestCase
{
    /**
     * @var AdminController
     */
    protected $adminController;

    protected function setUp(): void
    {
        parent::setUp();

        $this->login();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->adminController);
    }

    public function testIndexAction(): void
    {
        $this->client->request('GET', '/admin/');
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        self::assertSelectorTextContains('main h2', 'Overzicht');
    }

    public function testIndexActionNotAdmin(): void
    {
        $this->logout();
        $this->login(['ROLE_AUTHOR']);
        $this->client->request('GET', '/admin/');
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        self::assertSelectorTextContains('main h2', 'Overzicht');
    }
}
