<?php

namespace Tests\Unit\Form\Security;

use App\Form\Security\LocalAccountType;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class LocalAccountTypeTest.
 *
 * @covers \App\Form\Security\LocalAccountType
 */
class LocalAccountTypeTest extends KernelTestCase
{
    /**
     * @var LocalAccountType
     */
    protected $localAccountType;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        /** @todo Correctly instantiate tested object to use it. */
        $this->localAccountType = new LocalAccountType();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->localAccountType);
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
