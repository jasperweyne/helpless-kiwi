<?php

namespace Tests\Unit\Form\Activity;

use App\Form\Activity\PresentType;
use App\Provider\Person\PersonRegistry;
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
     * @var PersonRegistry
     */
    protected $personRegistry;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        $this->personRegistry = self::$container->get(PersonRegistry::class);
        $this->presentType = new PresentType($this->personRegistry);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->presentType);
        unset($this->personRegistry);
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
