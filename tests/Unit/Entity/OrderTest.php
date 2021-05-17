<?php

namespace Tests\Unit\Entity;

use App\Entity\Order;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class OrderTest.
 *
 * @covers \App\Entity\Order
 */
class OrderTest extends KernelTestCase
{
    /**
     * @var Order
     */
    protected $order;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        /* @todo Correctly instantiate tested object to use it. */
        $this->order = Order::create('test');
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->order);
    }

    public function test__toString(): void
    {
        /* @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testAvg(): void
    {
        /* @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testCreate(): void
    {
        /* @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testCalc(): void
    {
        /* @todo This test is incomplete. */
        $this->markTestIncomplete();
    }
}
