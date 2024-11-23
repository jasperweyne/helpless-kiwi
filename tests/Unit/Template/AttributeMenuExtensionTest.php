<?php

namespace Tests\Unit\Template;

use App\Template\Attribute\MenuItem;
use App\Template\AttributeMenuExtension;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class AttributeMenuExtensionTest.
 *
 * @covers \App\Template\AttributeMenuExtension
 */
class AttributeMenuExtensionTest extends KernelTestCase
{
    protected AttributeMenuExtension $attributeMenuExtension;

    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        $this->attributeMenuExtension = new AttributeMenuExtension('Tests\Unit\Template', __DIR__, '');
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->attributeMenuExtension);
    }

    #[MenuItem('test1', path: '/')]
    public function testGetMenuItems(): void
    {
        $items = $this->attributeMenuExtension->getMenuItems();

        self::assertNotEmpty($items);
        self::assertEquals('test1', reset($items)['title']);
    }

    #[MenuItem('testOther', path: '/', menu: 'other')]
    public function testGetMenuItemsOther(): void
    {
        $items = $this->attributeMenuExtension->getMenuItems('other');

        self::assertNotEmpty($items);
        self::assertCount(1, $items);
        self::assertEquals('testOther', reset($items)['title']);
    }

    #[MenuItem('testDup1', path: '/1', menu: 'duplicate')]
    #[MenuItem('testDup2', path: '/2', menu: 'duplicate')]
    public function testGetMenuItemsDuplicate(): void
    {
        $items = $this->attributeMenuExtension->getMenuItems('duplicate');

        self::assertCount(2, $items);
    }
}
