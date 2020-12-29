<?php

namespace Tests\Unit\Log;

use App\Log\EventService;
use App\Reflection\ReflectionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class EventServiceTest.
 *
 * @covers \App\Log\EventService
 */
class EventServiceTest extends KernelTestCase
{
    /**
     * @var EventService
     */
    protected $eventService;

    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var ReflectionService
     */
    protected $refl;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        $this->em = self::$container->get(EntityManagerInterface::class);
        $this->tokenStorage = self::$container->get(TokenStorageInterface::class);
        $this->refl = self::$container->get(ReflectionService::class);
        $this->eventService = new EventService($this->em, $this->tokenStorage, $this->refl);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->eventService);
        unset($this->em);
        unset($this->tokenStorage);
        unset($this->refl);
    }

    public function testLog(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testHydrate(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testPopulate(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testPopulateAll(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testFindBy(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testFindOneBy(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testGetIdentifier(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testGetClassName(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }
}
