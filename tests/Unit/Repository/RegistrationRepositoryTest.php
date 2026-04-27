<?php

namespace Tests\Unit\Repository;

use App\Repository\RegistrationRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Hautelook\AliceBundle\PhpUnit\RecreateDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class RegistrationRepositoryTest.
 *
 * @covers \App\Repository\RegistrationRepository
 */
class RegistrationRepositoryTest extends KernelTestCase
{
    use RecreateDatabaseTrait;

    protected ObjectManager $em;
    protected RegistrationRepository $registrationRepository;
    protected ManagerRegistry $registry;

    protected function setUp(): void
    {
        parent::setUp();
        $kernel = self::bootKernel();

        $this->registry = self::getContainer()->get(ManagerRegistry::class);
        $this->registrationRepository = new RegistrationRepository($this->registry);

        $this->em = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->registrationRepository);
        unset($this->registry);
    }

    public function testFindDeregistrations(): void
    {
        /* @todo This test is incomplete. */
        self::markTestIncomplete();
    }
}
