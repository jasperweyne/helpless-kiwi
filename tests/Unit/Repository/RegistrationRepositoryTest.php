<?php

namespace Tests\Unit\Repository;

use App\Entity\Activity\Activity;
use App\Entity\Activity\Registration;
use App\Repository\RegistrationRepository;
use Doctrine\Common\Collections\ArrayCollection;
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

    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        $this->registry = self::getContainer()->get(ManagerRegistry::class);
        $this->registrationRepository = new RegistrationRepository($this->registry);

        $this->em = static::$kernel->getContainer()
            ->get('doctrine')
            ->getManager();
    }

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
        })->toArray();

        $prepend = strval($this->em->getRepository(Registration::class)->findPrependPosition($activity));
        $registrations[] = $prepend;

        // sort positions
        sort($registrations);
        $registrations = new ArrayCollection($registrations);

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
        })->toArray();

        $append = strval($this->em->getRepository(Registration::class)->findAppendPosition($activity));
        $registrations[] = $append;

        // sort positions
        sort($registrations);
        $registrations = new ArrayCollection($registrations);

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
