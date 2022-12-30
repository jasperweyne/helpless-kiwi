<?php

namespace Tests\Unit\Template\Attribute;

use App\Template\Attribute\MenuItem;
use ReflectionClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class MenuItemTest.
 *
 * @covers \App\Template\Attribute\MenuItem
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
        $this->menuItem = new MenuItem('test');
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->menuItem);
    }

    public function testToMenuItemArray(): void
    {
        /* @todo This test is incomplete. */
        self::markTestIncomplete();
    }
}
