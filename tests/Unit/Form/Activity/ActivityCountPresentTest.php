<?php

namespace Tests\Unit\Form\Activity;

use App\Form\Activity\ActivityCountPresent;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class ActivityCountPresentTest.
 *
 * @covers \App\Form\Activity\ActivityCountPresent
 */
class ActivityCountPresentTest extends KernelTestCase
{
    /**
     * @var ActivityCountPresent
     */
    protected $activityCountPresent;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        /** @todo Correctly instantiate tested object to use it. */
        $this->activityCountPresent = new ActivityCountPresent();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->activityCountPresent);
    }

    public function testBuildForm(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testConfigureOptions(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }
}
