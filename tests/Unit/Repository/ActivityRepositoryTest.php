<?php

namespace Tests\Unit\Repository;

use App\Entity\Activity\Activity;
use App\Entity\Group\Group;
use App\Repository\ActivityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Hautelook\AliceBundle\PhpUnit\RecreateDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class ActivityRepositoryTest.
 *
 * @covers \App\Repository\ActivityRepository
 */
class ActivityRepositoryTest extends KernelTestCase
{
    use RecreateDatabaseTrait;

    protected ObjectManager $em;

    protected ActivityRepository $activityRepository;

    protected ManagerRegistry $registry;

    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        $this->registry = self::getContainer()->get(ManagerRegistry::class);
        $this->activityRepository = new ActivityRepository($this->registry);

        $this->em = static::$kernel->getContainer()
            ->get('doctrine')
            ->getManager();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->activityRepository);
        unset($this->registry);
    }

    public function testFindAuthor(): void
    {
        $groups = $this->em->getRepository(Group::class)->findAll();

        $activities = $this->em
            ->getRepository(Activity::class)
            ->findAuthor($groups);

        foreach ($activities as $activitie) {
            self::assertTrue(in_array($activitie->getAuthor(), $groups, true));
        }

        self::assertTrue(count($activities) > 0);
    }

    public function testFindAuthorArchive(): void
    {
        /* @todo This test is incomplete. */
        self::markTestIncomplete();
    }

    public function testFindUpcoming(): void
    {
        /* @todo This test is incomplete. */
        self::markTestIncomplete();
    }

    public function testFindActive(): void
    {
        $activities = $this->em
            ->getRepository(Activity::class)
            ->findActive();

        foreach ($activities as $activitie) {
            self::assertFalse($activitie->getArchived());
        }

        self::assertTrue(count($activities) > 0);
    }

    public function testFindArchived(): void
    {
        $activities = $this->em
            ->getRepository(Activity::class)
            ->findArchived();

        foreach ($activities as $activitie) {
            self::assertTrue($activitie->getArchived());
        }

        self::assertTrue(0 == count($activities));
    }

    public function testFindUpcomingByGroup(): void
    {
        /* @todo This test is incomplete. */
        self::markTestIncomplete();
    }

    public function testVisibleUpcomingByGroup(): void
    {
        $groups = $this->em->getRepository(Group::class)->findAll();

        $activities = $this->em
            ->getRepository(Activity::class)
            ->findVisibleUpcomingByGroup($groups);

        foreach ($activities as $activitie) {
            self::assertTrue(in_array($activitie->getAuthor(), $groups, true));
        }

        self::assertTrue(count($activities) > 0);
    }

    public function testArchiveAndActiveMatchAll(): void
    {
        $repo = $this->em
            ->getRepository(Activity::class);
        $active = $repo->findActive();
        $archive = $repo->findArchived();
        $all = $repo->findAll();

        $activeAndArchive = array_merge($active, $archive);
        self::assertEqualsCanonicalizing($all, $activeAndArchive);
    }
}
