<?php

namespace Tests\Unit\Repository;

use App\Entity\Group\Group;
use App\Entity\Security\LocalAccount;
use App\Repository\GroupRepository;
use App\Tests\Database\Activity\RegistrationFixture;
use App\Tests\Database\Group\GroupFixture;
use App\Tests\Database\Group\RelationFixture;
use App\Tests\Database\Security\LocalAccountFixture;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class GroupRepositoryTest.
 *
 * @covers \App\Repository\GroupRepository
 */
class GroupRepositoryTest extends KernelTestCase
{
    /**
     * @var AbstractDatabaseTool
     */
    protected $databaseTool;

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

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        $this->registry = self::getContainer()->get(ManagerRegistry::class);
        $this->groupRepository = new GroupRepository($this->registry);

        // Get all database tables
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $cmf = $em->getMetadataFactory();
        $classes = $cmf->getAllMetadata();

        // Write all tables to database
        $schema = new SchemaTool($em);
        $schema->createSchema($classes);

        // Load database tool
        $this->databaseTool = static::getContainer()->get(DatabaseToolCollection::class)->get();

        $this->databaseTool->loadFixtures([
            RegistrationFixture::class,
            GroupFixture::class,
            LocalAccountFixture::class,
            RelationFixture::class,
        ]);

        $this->em = static::$kernel->getContainer()
            ->get('doctrine')
            ->getManager();
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

    public function testFindSubGroupsFor(): void
    {
        $registration = $this->em->getRepository(LocalAccount::class)->findAll()[0];
        $group = $registration->getRelations()[0];
        assert($group !== null);

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
