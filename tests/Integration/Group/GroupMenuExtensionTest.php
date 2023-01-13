<?php

namespace Tests\Integration\Group;

use App\Entity\Security\LocalAccount;
use App\Group\GroupMenuExtension;
use App\Tests\Database\Group\GroupFixture;
use App\Tests\Database\Group\RelationFixture;
use App\Tests\Database\Security\LocalAccountFixture;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Guard\Token\PostAuthenticationGuardToken;

/**
 * Class GroupMenuExtensionTest.
 *
 * @covers \App\Group\GroupMenuExtension
 */
class GroupMenuExtensionTest extends KernelTestCase
{
    /**
     * @var AbstractDatabaseTool
     */
    protected $databaseTool;

    /**
     * @var GroupMenuExtension
     */
    protected $groupMenuExtension;

    /**
     * @var LocalAccount
     */
    protected $user;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        // Get all database tables
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $cmf = $em->getMetadataFactory();
        $classes = $cmf->getAllMetadata();

        // Write all tables to database
        $schema = new SchemaTool($em);
        $schema->createSchema($classes);

        // Load database tool
        $this->databaseTool = static::getContainer()->get(DatabaseToolCollection::class)->get();

        // load database fixtures
        $this->databaseTool->loadFixtures([
            LocalAccountFixture::class,
            GroupFixture::class,
            RelationFixture::class,
        ]);

        // get user
        $users = $em->getRepository(LocalAccount::class)->findAll();
        assert(isset($users[0]));
        $this->user = $users[0];

        // build token storage
        $token = new PostAuthenticationGuardToken($this->user, 'main', ['ROLE_USER']);
        $tokenStorage = self::getContainer()->get(TokenStorageInterface::class);
        $tokenStorage->setToken($token);

        $this->groupMenuExtension = new GroupMenuExtension($tokenStorage);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->groupMenuExtension);
        unset($this->user);
    }

    public function testGetMenuItems(): void
    {
        $expected = [];
        self::assertSame($expected, $this->groupMenuExtension->getMenuItems());
    }

    public function testGetAdminMenuItems(): void
    {
        $expected = count($this->user->getActiveGroups());
        $result = $this->groupMenuExtension->getMenuItems('admin');
        self::assertCount(1, $result);
        self::assertSame('Activiteiten', $result[0]['title']);
        self::assertNotEquals(0, $expected);
        self::assertArrayHasKey('sub', $result[0]);
        self::assertCount($expected, $result[0]['sub']);
    }
}
