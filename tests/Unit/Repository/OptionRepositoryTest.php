<?php

namespace Tests\Unit\Repository;

use App\Repository\OptionRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class OptionRepositoryTest.
 *
 * @covers \App\Repository\OptionRepository
 */
class OptionRepositoryTest extends KernelTestCase
{
    /**
     * @var OptionRepository
     */
    protected $optionRepository;

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

        $this->registry = self::getContainer()->get(ManagerRegistry::class);
        $this->optionRepository = new OptionRepository($this->registry);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->optionRepository);
        unset($this->registry);
    }

    public function testFindUpcomingByGroup(): void
    {
        /* @todo This test is incomplete. */
        self::markTestIncomplete();
    }
}
