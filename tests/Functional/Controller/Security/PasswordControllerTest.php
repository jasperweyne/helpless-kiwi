<?php

namespace Tests\Functional\Controller\Security;

use App\Controller\Security\PasswordController;
use App\Security\PasswordResetService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Class PasswordControllerTest.
 *
 * @covers \App\Controller\Security\PasswordController
 */
class PasswordControllerTest extends WebTestCase
{
    /**
     * @var PasswordController
     */
    protected $passwordController;

    /**
     * @var UserPasswordEncoderInterface
     */
    protected $passwordEncoder;

    /**
     * @var PasswordResetService
     */
    protected $passwordReset;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        $this->passwordEncoder = self::$container->get(UserPasswordEncoderInterface::class);
        $this->passwordReset = self::$container->get(PasswordResetService::class);
        $this->passwordController = new PasswordController($this->passwordEncoder, $this->passwordReset);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->passwordController);
        unset($this->passwordEncoder);
        unset($this->passwordReset);
    }

    public function testResetAction(): void
    {
        /* @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testRegisterAction(): void
    {
        /* @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testRequestAction(): void
    {
        /* @todo This test is incomplete. */
        $this->markTestIncomplete();
    }
}
