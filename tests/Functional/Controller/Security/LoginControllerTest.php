<?php

namespace Tests\Functional\Controller\Security;

use App\Controller\Security\LoginController;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class LoginControllerTest.
 *
 * @covers \App\Controller\Security\LoginController
 */
class LoginControllerTest extends WebTestCase
{
    /**
     * @var LoginController
     */
    protected $loginController;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        /* @todo Correctly instantiate tested object to use it. */
        $this->loginController = new LoginController();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->loginController);
    }

    public function testLogin(): void
    {
        /* @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testLogout(): void
    {
        /* @todo This test is incomplete. */
        $this->markTestIncomplete();
    }
}
