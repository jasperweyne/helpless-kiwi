<?php

namespace Tests\Unit\Template;

use App\Template\MenuBuilder;
use ReflectionClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class MenuBuilderTest.
 *
 * @covers \App\Template\MenuBuilder
 */
class MenuBuilderTest extends KernelTestCase
{
    /**
     * @var MenuBuilder
     */
    protected $menuBuilder;

    /**
     * @var mixed
     */
    protected $extensions;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        $this->extensions = null;
        $this->menuBuilder = new MenuBuilder($this->extensions);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->menuBuilder);
        unset($this->extensions);
    }

    public function testGetExtensions(): void
    {
        $expected = [];
        $property = (new ReflectionClass(MenuBuilder::class))
            ->getProperty('extensions');
        $property->setAccessible(true);
        $property->setValue($this->menuBuilder, $expected);
        self::assertSame($expected, $this->menuBuilder->getExtensions());
    }

    public function testGetItems(): void
    {
        /* @todo This test is incomplete. */
        self::markTestIncomplete();
    }
}
