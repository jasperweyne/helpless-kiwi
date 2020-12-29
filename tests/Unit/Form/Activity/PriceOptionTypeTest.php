<?php

namespace Tests\Unit\Form\Activity;

use App\Form\Activity\PriceOptionType;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class PriceOptionTypeTest.
 *
 * @covers \App\Form\Activity\PriceOptionType
 */
class PriceOptionTypeTest extends KernelTestCase
{
    /**
     * @var PriceOptionType
     */
    protected $priceOptionType;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        /** @todo Correctly instantiate tested object to use it. */
        $this->priceOptionType = new PriceOptionType();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->priceOptionType);
    }

    public function testBuildForm(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testConfigureOptions(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }
}
