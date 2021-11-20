<?php

namespace Tests\Integration\Form\Activity;

use App\Form\Activity\PresentType;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class PresentTypeTest.
 *
 * @covers \App\Form\Activity\PresentType
 */
class PresentTypeTest extends KernelTestCase
{
    /**
     * @var PresentType
     */
    protected $presentType;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        $this->presentType = new PresentType();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->presentType);
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
