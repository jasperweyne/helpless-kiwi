<?php

namespace Tests\Unit\Provider\Person;

use App\Provider\Person\PersonRegistry;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class PersonRegistryTest.
 *
 * @covers \App\Provider\Person\PersonRegistry
 */
class PersonRegistryTest extends KernelTestCase
{
    /**
     * @var PersonRegistry
     */
    protected $personRegistry;

    /**
     * @var mixed
     */
    protected $providers;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        $this->providers = null;
        $this->personRegistry = new PersonRegistry($this->providers);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->personRegistry);
        unset($this->providers);
    }

    public function testFind(): void
    {
        /* @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testFindAll(): void
    {
        /* @todo This test is incomplete. */
        $this->markTestIncomplete();
    }
}
