<?php

namespace Tests\Unit\Template\Annotation;

use App\Template\Annotation\MenuItem;
use ReflectionClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class MenuItemTest.
 *
 * @covers \App\Template\Annotation\MenuItem
 */
class MenuItemTest extends KernelTestCase
{
    /**
     * @var MenuItem
     */
    protected $menuItem;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        /* @todo Correctly instantiate tested object to use it. */
        $this->menuItem = new MenuItem();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->menuItem);
    }

    public function testGetTitle(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(MenuItem::class))
            ->getProperty('title');
        $property->setValue($this->menuItem, $expected);
        self::assertSame($expected, $this->menuItem->getTitle());
    }

    public function testGetMenu(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(MenuItem::class))
            ->getProperty('menu');
        $property->setValue($this->menuItem, $expected);
        self::assertSame($expected, $this->menuItem->getMenu());
    }

    public function testGetRole(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(MenuItem::class))
            ->getProperty('role');
        $property->setValue($this->menuItem, $expected);
        self::assertSame($expected, $this->menuItem->getRole());
    }

    public function testGetClass(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(MenuItem::class))
            ->getProperty('class');
        $property->setValue($this->menuItem, $expected);
        self::assertSame($expected, $this->menuItem->getClass());
    }

    public function testGetActiveCriteria(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(MenuItem::class))
            ->getProperty('activeCriteria');
        $property->setValue($this->menuItem, $expected);
        self::assertSame($expected, $this->menuItem->getActiveCriteria());
    }

    public function testGetOrder(): void
    {
        $expected = 42;
        $property = (new ReflectionClass(MenuItem::class))
            ->getProperty('order');
        $property->setValue($this->menuItem, $expected);
        self::assertSame($expected, $this->menuItem->getOrder());
    }

    public function testGetPath(): void
    {
        $expected = null;
        $property = (new ReflectionClass(MenuItem::class))
            ->getProperty('path');
        $property->setAccessible(true);
        $property->setValue($this->menuItem, $expected);
        self::assertSame($expected, $this->menuItem->getPath());
    }

    public function testSetPath(): void
    {
        $expected = null;
        $property = (new ReflectionClass(MenuItem::class))
            ->getProperty('path');
        $property->setAccessible(true);
        $this->menuItem->setPath($expected);
        self::assertSame($expected, $property->getValue($this->menuItem));
    }
}
