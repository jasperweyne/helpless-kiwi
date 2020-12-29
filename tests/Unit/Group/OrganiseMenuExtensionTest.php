<?php

namespace Tests\Unit\Group;

use App\Group\OrganiseMenuExtension;
use Doctrine\ORM\EntityManagerInterface;
use ReflectionClass;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class OrganiseMenuExtensionTest.
 *
 * @covers \App\Group\OrganiseMenuExtension
 */
class OrganiseMenuExtensionTest extends KernelTestCase
{
    /**
     * @var OrganiseMenuExtension
     */
    protected $organiseMenuExtension;

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
        $this->organiseMenuExtension = new OrganiseMenuExtension($this->em, $this->tokenStorage);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->organiseMenuExtension);
        unset($this->em);
        unset($this->tokenStorage);
    }

    public function testGetMenuItems(): void
    {
        $expected = null;
        $property = (new ReflectionClass(OrganiseMenuExtension::class))
            ->getProperty('menuItems');
        $property->setAccessible(true);
        $property->setValue($this->organiseMenuExtension, $expected);
        $this->assertSame($expected, $this->organiseMenuExtension->getMenuItems());
    }
}
