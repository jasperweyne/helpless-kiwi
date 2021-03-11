<?php

namespace Tests\Unit\Repository;

use App\Repository\RegistrationRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class RegistrationRepositoryTest.
 *
 * @covers \App\Repository\RegistrationRepository
 */
class RegistrationRepositoryTest extends KernelTestCase
{
    /**
     * @var RegistrationRepository
     */
    protected $registrationRepository;

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
        $this->registrationRepository = new RegistrationRepository($this->registry);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->registrationRepository);
        unset($this->registry);
    }

    public function testMINORDER(): void
    {
        /* @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testMAXORDER(): void
    {
        /* @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testFindPrependPosition(): void
    {
        /* @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testFindAppendPosition(): void
    {
        /* @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testFindBefore(): void
    {
        /* @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testFindAfter(): void
    {
        /* @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testFindDeregistrations(): void
    {
        /* @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testFindReserve(): void
    {
        /* @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testCountPresent(): void
    {
        /* @todo This test is incomplete. */
        $this->markTestIncomplete();
    }
}
