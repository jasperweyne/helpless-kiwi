<?php

namespace Tests\Functional\Controller\Admin;

use App\Controller\Admin\AdminController;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class AdminControllerTest.
 *
 * @covers \App\Controller\Admin\AdminController
 */
class AdminControllerTest extends WebTestCase
{
    /**
     * @var AdminController
     */
    protected $adminController;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        /** @todo Correctly instantiate tested object to use it. */
        $this->adminController = new AdminController();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->adminController);
    }

    public function testIndexAction(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }
}
