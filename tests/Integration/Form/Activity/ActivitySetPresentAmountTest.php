<?php

namespace Tests\Integration\Form\Activity;

use App\Form\Activity\ActivitySetPresentAmount;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class ActivitySetPresentAmountTest.
 *
 * @covers \App\Form\Activity\ActivitySetPresentAmount
 */
class ActivitySetPresentAmountTest extends KernelTestCase
{
    /**
     * @var ActivitySetPresentAmount
     */
    protected $activitySetPresentAmount;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        /* @todo Correctly instantiate tested object to use it. */
        $this->activitySetPresentAmount = new ActivitySetPresentAmount();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->activitySetPresentAmount);
    }

    public function testBuildForm(): void
    {
        /* @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testConfigureOptions(): void
    {
        /* @todo This test is incomplete. */
        $this->markTestIncomplete();
    }
}
