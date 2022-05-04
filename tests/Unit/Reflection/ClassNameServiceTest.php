<?php

namespace Tests\Unit\Reflection;

use App\Reflection\ClassNameService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class ClassNameServiceTest.
 *
 * @covers \App\Reflection\ClassNameService
 */
class ClassNameServiceTest extends KernelTestCase
{
    /**
     * @var ClassNameService
     */
    protected $classNameService;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        /* @todo Correctly instantiate tested object to use it. */
        $this->classNameService = new ClassNameService();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->classNameService);
    }

    public function testFqcnToName(): void
    {
        /* @todo This test is incomplete. */
        self::markTestIncomplete();
    }
}
