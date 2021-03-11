<?php

namespace Tests\Functional\Controller\Admin;

use App\Controller\Admin\MailController;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class MailControllerTest.
 *
 * @covers \App\Controller\Admin\MailController
 */
class MailControllerTest extends WebTestCase
{
    /**
     * @var MailController
     */
    protected $mailController;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        /* @todo Correctly instantiate tested object to use it. */
        $this->mailController = new MailController();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->mailController);
    }

    public function testIndexAction(): void
    {
        /* @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testShowAction(): void
    {
        /* @todo This test is incomplete. */
        $this->markTestIncomplete();
    }
}
