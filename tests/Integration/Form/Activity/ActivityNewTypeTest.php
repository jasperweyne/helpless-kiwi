<?php

namespace Tests\Integration\Form\Activity;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class ActivityNewTypeTest.
 *
 * @covers \App\Form\Activity\ActivityNewType
 */
class ActivityNewTypeTest extends KernelTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();
    }

    public function testBuildForm(): void
    {
        /* @todo This test is incomplete. */
        self::markTestIncomplete();
    }
}
