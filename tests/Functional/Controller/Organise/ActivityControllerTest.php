<?php

namespace Tests\Functional\Controller\Organise;

use App\Controller\Organise\ActivityController;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class ActivityControllerTest.
 *
 * @covers \App\Controller\Organise\ActivityController
 */
class ActivityControllerTest extends WebTestCase
{
    /**
     * @var ActivityController
     */
    protected $activityController;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        /** @todo Correctly instantiate tested object to use it. */
        $this->activityController = new ActivityController();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->activityController);
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
