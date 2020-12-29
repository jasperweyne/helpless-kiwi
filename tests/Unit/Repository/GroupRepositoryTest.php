<?php

namespace Tests\Unit\Repository;

use App\Repository\GroupRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class GroupRepositoryTest.
 *
 * @covers \App\Repository\GroupRepository
 */
class GroupRepositoryTest extends KernelTestCase
{
    /**
     * @var GroupRepository
     */
    protected $groupRepository;

    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        $this->registry = self::$container->get(ManagerRegistry::class);
        $this->groupRepository = new GroupRepository($this->registry);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->groupRepository);
        unset($this->registry);
    }

    public function testFindAllFor(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }
}
