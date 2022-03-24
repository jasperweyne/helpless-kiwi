<?php

namespace Tests\Integration\Form\Activity;

use App\Form\Activity\ActivityEditPresent;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class ActivityEditPresentTest.
 *
 * @covers \App\Form\Activity\ActivityEditPresent
 */
class ActivityEditPresentTest extends KernelTestCase
{
    /**
     * @var ActivityEditPresent
     */
    protected $activityEditPresent;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        /* @todo Correctly instantiate tested object to use it. */
        $this->activityEditPresent = new ActivityEditPresent();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->activityEditPresent);
    }

    public function testBuildForm(): void
    {
        /* @todo This test is incomplete. */
        self::markTestIncomplete();
    }

    public function testConfigureOptions(): void
    {
        /* @todo This test is incomplete. */
        self::markTestIncomplete();
    }
}
