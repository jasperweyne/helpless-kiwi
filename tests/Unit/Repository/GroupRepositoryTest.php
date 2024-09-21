<?php

namespace Tests\Unit\Repository;

use App\Entity\Security\LocalAccount;
use App\Repository\GroupRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Hautelook\AliceBundle\PhpUnit\RecreateDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class GroupRepositoryTest.
 *
 * @covers \App\Repository\GroupRepository
 */
class GroupRepositoryTest extends KernelTestCase
{
    use RecreateDatabaseTrait;

    /**
     * @var ObjectManager
     */
    protected $em;

    /**
     * @var GroupRepository
     */
    protected $groupRepository;

    /**
     * @var ManagerRegistry
     */
    protected $registry;

    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        $this->registry = self::getContainer()->get(ManagerRegistry::class);
        $this->groupRepository = new GroupRepository($this->registry);

        $this->em = static::$kernel->getContainer()
            ->get('doctrine')
            ->getManager();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->groupRepository);
        unset($this->registry);
    }

    public function testFindSubGroupsFor(): void
    {
        $registration = $this->em->getRepository(LocalAccount::class)->findAll()[0];
        $group = $registration->getRelations()[0];
        assert(null !== $group);

        $allgroups = $this->groupRepository->findSubGroupsFor($group);

        foreach ($allgroups as $parent) {
            // keep iterating over parents until $group is found
            while (true) {
                $parent = $parent->getParent();
                if ($parent == $group) {
                    // $parent is in hierarchy, success! Continue with next $g
                    break;
                }
                self::assertNotNull($parent);
            }
        }

        self::assertTrue(count($allgroups) > 0);
    }

    public function testFindSubGroupsForPerson(): void
    {
        $registration = $this->em->getRepository(LocalAccount::class)->findAll()[0];

        $allgroups = $this->groupRepository->findSubGroupsForPerson($registration);
        $parents = $registration->getRelations()->toArray();

        foreach ($allgroups as $parent) {
            // keep iterating over parents until $group is found
            while (true) {
                if (in_array($parent, $parents, true)) {
                    // $parent is in hierarchy, success! Continue with next $g
                    break;
                }
                self::assertNotNull($parent);
                $parent = $parent->getParent();
            }
        }

        self::assertTrue(count($allgroups) > 1);
    }
}
