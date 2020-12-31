<?php

namespace Tests\Unit\Provider;

use App\Provider\LocalDataProvider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class LocalDataProviderTest.
 *
 * @covers \App\Provider\LocalDataProvider
 */
class LocalDataProviderTest extends KernelTestCase
{
    /**
     * @var LocalDataProvider
     */
    protected $localDataProvider;

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
        $this->localDataProvider = new LocalDataProvider($this->em);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->localDataProvider);
        unset($this->em);
    }

    public function testFindPerson(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testFindPersons(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }
}
