<?php

namespace Tests\Unit\Form\Activity\Admin;

use App\Form\Activity\Admin\ActivityNewType;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class ActivityNewTypeTest.
 *
 * @covers \App\Form\Activity\Admin\ActivityNewType
 */
class ActivityNewTypeTest extends KernelTestCase
{
    /**
     * @var ActivityNewType
     */
    protected $activityNewType;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        /** @todo Correctly instantiate tested object to use it. */
        $this->activityNewType = new ActivityNewType();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->activityNewType);
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
