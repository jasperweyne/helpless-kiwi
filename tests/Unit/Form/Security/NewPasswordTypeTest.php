<?php

namespace Tests\Unit\Form\Security;

use App\Form\Security\NewPasswordType;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class NewPasswordTypeTest.
 *
 * @covers \App\Form\Security\NewPasswordType
 */
class NewPasswordTypeTest extends KernelTestCase
{
    /**
     * @var NewPasswordType
     */
    protected $newPasswordType;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        /* @todo Correctly instantiate tested object to use it. */
        $this->newPasswordType = new NewPasswordType();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->newPasswordType);
    }

    public function testBuildForm(): void
    {
        /* @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testConfigureOptions(): void
    {
        /* @todo This test is incomplete. */
        $this->markTestIncomplete();
    }
}
