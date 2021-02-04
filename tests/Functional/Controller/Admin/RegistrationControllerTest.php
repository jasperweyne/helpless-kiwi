<?php

namespace Tests\Functional\Controller\Admin;

use App\Controller\Admin\RegistrationController;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class RegistrationControllerTest.
 *
 * @covers \App\Controller\Admin\RegistrationController
 */
class RegistrationControllerTest extends WebTestCase
{
    /**
     * @var RegistrationController
     */
    protected $registrationController;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        /* @todo Correctly instantiate tested object to use it. */
        $this->registrationController = new RegistrationController();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->registrationController);
    }

    public function testNewAction(): void
    {
        /* @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testDeleteAction(): void
    {
        /* @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testReserveNewAction(): void
    {
        /* @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testReserveMoveUpAction(): void
    {
        /* @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testReserveMoveDownAction(): void
    {
        /* @todo This test is incomplete. */
        $this->markTestIncomplete();
    }
}
