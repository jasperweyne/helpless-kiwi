<?php

namespace Tests\Unit\Repository;

use App\Repository\RegistrationRepository;
use App\Entity\Activity\Activity;
use App\Entity\Activity\Registration;
use App\Tests\Database\Activity\ActivityFixture;
use App\Tests\Database\Activity\RegistrationFixture;
use App\Tests\Database\Group\GroupFixture;
use App\Tests\Database\Group\RelationFixture;
use App\Tests\Database\Security\LocalAccountFixture;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Order;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class RegistrationRepositoryTest.
 *
 * @covers \App\Repository\RegistrationRepository
 */
class RegistrationRepositoryTest extends KernelTestCase
{
    use FixturesTrait;

    /**
     * @var ObjectManager
     */
    protected $em;

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

        // Get all database tables
        $em = self::$container->get(EntityManagerInterface::class);
        $cmf = $em->getMetadataFactory();
        $classes = $cmf->getAllMetadata();

        // Write all tables to database
        $schema = new SchemaTool($em);
        $schema->createSchema($classes);

        $this->loadFixtures([
            RegistrationFixture::class,
            GroupFixture::class,
            ActivityFixture::class,
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

        unset($this->registrationRepository);
        unset($this->registry);
    }

    public function testMINORDER(): void
    {
        /* @todo This test is incomplete. */
        self::markTestIncomplete();
    }

    public function testMAXORDER(): void
    {
        /* @todo This test is incomplete. */
        self::markTestIncomplete();
    }

    public function testFindPrependPosition(): void
    {
        /** @var Activity $activity */
        $activity = $this->em->getRepository(Activity::class)->findAll()[0];

        /** @var Order $position */
        $position = $this->em->getRepository(Registration::class)->findPrependPosition($activity);
        $this->assertNotSame($position, Order::avg($this->em->getRepository(Registration::class)::MINORDER(), $this->em->getRepository(Registration::class)::MAXORDER()));
    }

    public function testFindAppendPosition(): void
    {
        /** @var Activity $activity */
        $activity = $this->em->getRepository(Activity::class)->findAll()[0];

        /** @var Order $position */
        $position = $this->em->getRepository(Registration::class)->findPrependPosition($activity);
        $this->assertNotSame($position, Order::avg($this->em->getRepository(Registration::class)::MINORDER(), $this->em->getRepository(Registration::class)::MAXORDER()));
    }

    public function testFindBefore(): void
    {
        /* @todo This test is incomplete. */
        self::markTestIncomplete();
    }

    public function testFindAfter(): void
    {
        /* @todo This test is incomplete. */
        self::markTestIncomplete();
    }

    public function testFindDeregistrations(): void
    {
        /* @todo This test is incomplete. */
        self::markTestIncomplete();
    }

    public function testFindReserve(): void
    {
        /* @todo This test is incomplete. */
        self::markTestIncomplete();
    }

    public function testCountPresent(): void
    {
        /* @todo This test is incomplete. */
        self::markTestIncomplete();
    }
}
