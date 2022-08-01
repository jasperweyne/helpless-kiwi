<?php

namespace Tests\Unit\Security;

use App\Security\LocalAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

/**
 * Class LocalAuthenticatorTest.
 *
 * @covers \App\Security\LocalAuthenticator
 */
class LocalAuthenticatorTest extends KernelTestCase
{
    /**
     * @var LocalAuthenticator
     */
    protected $localAuthenticator;

    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var UrlGeneratorInterface
     */
    protected $urlGenerator;

    /**
     * @var CsrfTokenManagerInterface
     */
    protected $csrfTokenManager;

    /**
     * @var UserPasswordEncoderInterface
     */
    protected $passwordEncoder;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        $this->em = self::$container->get(EntityManagerInterface::class);
        $this->urlGenerator = self::$container->get(UrlGeneratorInterface::class);
        $this->csrfTokenManager = self::$container->get(CsrfTokenManagerInterface::class);
        $this->passwordEncoder = self::$container->get(UserPasswordEncoderInterface::class);
        $this->localAuthenticator = new LocalAuthenticator($this->em, $this->urlGenerator, $this->csrfTokenManager, $this->passwordEncoder);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->localAuthenticator);
        unset($this->em);
        unset($this->urlGenerator);
        unset($this->csrfTokenManager);
        unset($this->passwordEncoder);
    }

    public function testSupports(): void
    {
        /* @todo This test is incomplete. */
        self::markTestIncomplete();
    }

    public function testGetCredentials(): void
    {
        /* @todo This test is incomplete. */
        self::markTestIncomplete();
    }

    public function testGetUser(): void
    {
        /* @todo This test is incomplete. */
        self::markTestIncomplete();
    }

    public function testCheckCredentials(): void
    {
        /* @todo This test is incomplete. */
        self::markTestIncomplete();
    }

    public function testOnAuthenticationSuccess(): void
    {
        /* @todo This test is incomplete. */
        self::markTestIncomplete();
    }
}
