<?php

namespace Tests\Integration\Form\Activity;

use App\Form\Activity\ActivityEditType;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class ActivityEditTypeTest.
 *
 * @covers \App\Form\Activity\ActivityEditType
 */
class ActivityEditTypeTest extends KernelTestCase
{
    /**
     * @var ActivityEditType
     */
    protected $activityEditType;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        /* @todo Correctly instantiate tested object to use it. */
        $this->activityEditType = new ActivityEditType();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->activityEditType);
    }

    public function testBuildForm(): void
    {
        /* @todo This test is incomplete. */
        $this->markTestIncomplete();
    }
}
