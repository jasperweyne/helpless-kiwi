<?php

namespace Tests\Unit\Security;

use App\Security\LocalUserProvider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class LocalUserProviderTest.
 *
 * @covers \App\Security\LocalUserProvider
 */
class LocalUserProviderTest extends KernelTestCase
{
    /**
     * @var LocalUserProvider
     */
    protected $localUserProvider;

    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        $this->em = self::$container->get(EntityManagerInterface::class);
        $this->localUserProvider = new LocalUserProvider($this->em);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->localUserProvider);
        unset($this->em);
    }

    public function testRefreshUser(): void
    {
        /* @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testSupportsClass(): void
    {
        /* @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testLoadUserByUsername(): void
    {
        /* @todo This test is incomplete. */
        $this->markTestIncomplete();
    }
}
