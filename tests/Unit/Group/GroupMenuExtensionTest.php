<?php

namespace Tests\Unit\Group;

use App\Group\GroupMenuExtension;
use Doctrine\ORM\EntityManagerInterface;
use ReflectionClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class GroupMenuExtensionTest.
 *
 * @covers \App\Group\GroupMenuExtension
 */
class GroupMenuExtensionTest extends KernelTestCase
{
    /**
     * @var GroupMenuExtension
     */
    protected $groupMenuExtension;

    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        $this->em = self::$container->get(EntityManagerInterface::class);
        $this->groupMenuExtension = new GroupMenuExtension($this->em);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->groupMenuExtension);
        unset($this->em);
    }

    public function testGetMenuItems(): void
    {
        $expected = [];
        $property = (new ReflectionClass(GroupMenuExtension::class))
            ->getProperty('menuItems');
        $property->setAccessible(true);
        $property->setValue($this->groupMenuExtension, $expected);
        $this->assertSame($expected, $this->groupMenuExtension->getMenuItems());
    }
}
