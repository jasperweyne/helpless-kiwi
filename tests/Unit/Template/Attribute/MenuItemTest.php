<?php

namespace Tests\Unit\Template\Attribute;

use App\Template\Attribute\MenuItem;
use App\Template\Attribute\SubmenuItem;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class MenuItemTest.
 *
 * @covers \App\Template\Attribute\MenuItem
 */
class MenuItemTest extends KernelTestCase
{
    public function testToMenuItemArray(): void
    {
        // Arrange
        $title = 'title';
        $menuItem = new MenuItem($title);

        // Act
        $result = $menuItem->toMenuItemArray();

        // Assert
        self::assertArrayNotHasKey('role', $result);
        self::assertArrayNotHasKey('class', $result);
        self::assertArrayNotHasKey('activeCriteria', $result);
        self::assertArrayNotHasKey('order', $result);
        self::assertArrayNotHasKey('sub', $result);
        self::assertArrayHasKey('path', $result);

        self::assertSame($title, $result['title']);
        self::assertSame('', $result['path']);
    }

    public function testToMenuItemArrayWithValues(): void
    {
        // Arrange
        $title = 'title';
        $menu = 'menu';
        $role = 'ROLE_ADMIN';
        $class = 'mobile';
        $activeCriteria = 'admin_title_';
        $order = -1;
        $sub = [
            new SubmenuItem('test', 'admin_title_sub'),
        ];
        $path = 'admin_title_index';
        $menuItem = new MenuItem($title, $menu, $role, $class, $activeCriteria, $order, $sub, $path);

        // Act
        $result = $menuItem->toMenuItemArray();

        // Assert
        self::assertArrayHasKey('role', $result);
        self::assertArrayHasKey('class', $result);
        self::assertArrayHasKey('activeCriteria', $result);
        self::assertArrayHasKey('order', $result);
        self::assertArrayHasKey('sub', $result);
        self::assertArrayHasKey('path', $result);

        self::assertSame($title, $result['title']);
        self::assertSame($role, $result['role']);
        self::assertSame($class, $result['class']);
        self::assertSame($activeCriteria, $result['activeCriteria']);
        self::assertSame($order, $result['order']);
        self::assertSame($path, $result['path']);
        self::assertCount(count($sub), $result['sub']);
    }
}
