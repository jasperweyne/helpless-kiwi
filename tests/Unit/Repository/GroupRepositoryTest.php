<?php

namespace Tests\Unit\Repository;

use App\Entity\Activity\Registration;
use App\Entity\Group\Group;
use App\Entity\Security\LocalAccount;
use App\Repository\GroupRepository;
use App\Tests\Database\Group\GroupFixture;
use App\Tests\Database\Group\RelationFixture;
use App\Tests\Database\Security\LocalAccountFixture;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\Persistence\ManagerRegistry;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class GroupRepositoryTest.
 *
 * @covers \App\Repository\GroupRepository
 */
class GroupRepositoryTest extends KernelTestCase
{
    use FixturesTrait;

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

        // Get all database tables
        $em = self::$container->get(EntityManagerInterface::class);
        $cmf = $em->getMetadataFactory();
        $classes = $cmf->getAllMetadata();

        // Write all tables to database
        $schema = new SchemaTool($em);
        $schema->createSchema($classes);

        $this->loadFixtures([
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

    public function testFindAllFor(): void
    {
        $registration = $this->em->getRepository(LocalAccount::class)->findAll()[0];
        $groups = $this->em->getRepository(Group::class)->findAllFor($registration);

        self::assertTrue(count($groups) > 0);
    }

    public function testFindSubGroupsFor(): void
    {
        $registration = $this->em->getRepository(Registration::class)->findAll()[0];
        $group = $this->em->getRepository(Group::class)->findAllFor($registration)[0];

        $allgroups = $this->em
            ->getRepository(Group::class)
            ->findSubGroupsFor($group);

        self::assertTrue(count($allgroups) > 0);
    }

    public function testFindSubGroupsForPerson(): void
    {
        $registration = $this->em->getRepository(Registration::class)->findAll()[0];

        $allgroups = $this->em
            ->getRepository(Group::class)
            ->findSubGroupsForPerson($registration);

        self::assertTrue(count($allgroups) > 1);
    }
}
