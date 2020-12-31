<?php

namespace Tests\Unit\Reflection;

use App\Reflection\ReflectionService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class ReflectionServiceTest.
 *
 * @covers \App\Reflection\ReflectionService
 */
class ReflectionServiceTest extends KernelTestCase
{
    /**
     * @var ReflectionService
     */
    protected $reflectionService;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        $this->reflectionService = new ReflectionService();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->reflectionService);
    }

    public function testInstantiate(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testGetAccessibleProperty(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testGetAllProperties(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }
}
