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
use Doctrine\Common\Collections\ArrayCollection;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Doctrine\Persistence\ObjectManager;

/**
 * Class RegistrationRepositoryTest.
 *
 * @covers \App\Repository\RegistrationRepository
 */
class RegistrationRepositoryTest extends KernelTestCase
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

        $this->registry = self::getContainer()->get(ManagerRegistry::class);
        $this->registrationRepository = new RegistrationRepository($this->registry);

        // Get all database tables
        /** @var EntityManagerInterface $em */
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

        // get list of stringified reserve positions
        $registrations = $activity->getRegistrations()->filter(function (Registration $registration) {
            return $registration->isReserve();
        })->map(function (Registration $registration) {
            return strval($registration->getReservePosition());
        });

        $prepend = strval($this->em->getRepository(Registration::class)->findPrependPosition($activity));
        $registrations[] = $prepend;

        // sort positions
        $regArray = $registrations->toArray();
        sort($regArray);
        $registrations = new ArrayCollection($regArray);

        // check that prepend position is first when list is ordered
        self::assertSame($prepend, $registrations->first());
    }

    public function testFindAppendPosition(): void
    {
        /** @var Activity $activity */
        $activity = $this->em->getRepository(Activity::class)->findAll()[0];

        // get list of stringified reserve positions
        $registrations = $activity->getRegistrations()->filter(function (Registration $registration) {
            return $registration->isReserve();
        })->map(function (Registration $registration) {
            return strval($registration->getReservePosition());
        });

        $append = strval($this->em->getRepository(Registration::class)->findAppendPosition($activity));
        $registrations[] = $append;

        // sort positions
        $regArray = $registrations->toArray();
        sort($regArray);
        $registrations = new ArrayCollection($regArray);

        // check that prepend position is first when list is ordered
        self::assertSame($append, $registrations->last());
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
}
