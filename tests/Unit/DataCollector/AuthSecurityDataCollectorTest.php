<?php

namespace Tests\Unit\DataCollector;

use App\DataCollector\AuthSecurityDataCollector;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class AuthSecurityDataCollectorTest.
 *
 * @covers \App\DataCollector\AuthSecurityDataCollector
 */
class AuthSecurityDataCollectorTest extends KernelTestCase
{
    /**
     * @var AuthSecurityDataCollector
     */
    protected $authSecurityDataCollector;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        /** @todo Correctly instantiate tested object to use it. */
        $this->authSecurityDataCollector = new AuthSecurityDataCollector();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->authSecurityDataCollector);
    }

    public function testCollect(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }
}
