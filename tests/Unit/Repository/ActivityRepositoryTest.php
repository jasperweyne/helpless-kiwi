<?php

namespace Tests\Unit\Repository;

use App\Repository\ActivityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class ActivityRepositoryTest.
 *
 * @covers \App\Repository\ActivityRepository
 */
class ActivityRepositoryTest extends KernelTestCase
{
    /**
     * @var ActivityRepository
     */
    protected $activityRepository;

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
        $this->activityRepository = new ActivityRepository($this->registry);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->activityRepository);
        unset($this->registry);
    }

    public function testFindUpcoming(): void
    {
        /* @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testFindUpcomingByGroup(): void
    {
        /* @todo This test is incomplete. */
        $this->markTestIncomplete();
    }
}
