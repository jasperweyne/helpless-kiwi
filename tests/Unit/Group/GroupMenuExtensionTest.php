<?php

namespace Tests\Unit\Group;

use App\Group\GroupMenuExtension;
use Doctrine\ORM\EntityManagerInterface;
use ReflectionClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

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
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        $this->em = self::$container->get(EntityManagerInterface::class);
        $this->tokenStorage = self::$container->get(TokenStorageInterface::class);
        $this->groupMenuExtension = new GroupMenuExtension($this->em, $this->tokenStorage);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->groupMenuExtension);
        unset($this->em);
        unset($this->tokenStorage);
    }

    public function testGetMenuItems(): void
    {
        $expected = [];
        $property = (new ReflectionClass(GroupMenuExtension::class))
            ->getProperty('menuItems');
        $property->setAccessible(true);
        $property->setValue($this->groupMenuExtension, $expected);
        self::assertSame($expected, $this->groupMenuExtension->getMenuItems());
    }
}
