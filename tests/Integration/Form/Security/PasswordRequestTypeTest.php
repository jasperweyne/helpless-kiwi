<?php

namespace Tests\Integration\Form\Security;

use App\Form\Security\PasswordRequestType;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class PasswordRequestTypeTest.
 *
 * @covers \App\Form\Security\PasswordRequestType
 */
class PasswordRequestTypeTest extends KernelTestCase
{
    /**
     * @var PasswordRequestType
     */
    protected $passwordRequestType;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        /* @todo Correctly instantiate tested object to use it. */
        $this->passwordRequestType = new PasswordRequestType();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->passwordRequestType);
    }

    public function testBuildForm(): void
    {
        /* @todo This test is incomplete. */
        self::markTestIncomplete();
    }

    public function testConfigureOptions(): void
    {
        /* @todo This test is incomplete. */
        self::markTestIncomplete();
    }
}
