<?php

namespace Tests\Unit\Form\Activity;

use App\Form\Activity\ActivityImageType;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class ActivityImageTypeTest.
 *
 * @covers \App\Form\Activity\ActivityImageType
 */
class ActivityImageTypeTest extends KernelTestCase
{
    /**
     * @var ActivityImageType
     */
    protected $activityImageType;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        /** @todo Correctly instantiate tested object to use it. */
        $this->activityImageType = new ActivityImageType();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->activityImageType);
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
