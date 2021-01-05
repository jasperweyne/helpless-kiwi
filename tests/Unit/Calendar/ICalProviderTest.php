<?php

namespace Tests\Unit\Calendar;

use App\Calendar\ICalProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class ICalProviderTest.
 *
 * @covers \App\Calendar\ICalProvider
 */
class ICalProviderTest extends KernelTestCase
{
    /**
     * @var ICalProvider
     */
    protected $iCalProvider;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        /* @todo Correctly instantiate tested object to use it. */
        $this->iCalProvider = new ICalProvider();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->iCalProvider);
    }

    public function testSingleEventIcal(): void
    {
        /* @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testIcalFeed(): void
    {
        /* @todo This test is incomplete. */
        $this->markTestIncomplete();
    }
}
