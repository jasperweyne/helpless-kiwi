<?php

namespace Tests\Unit\Security;

use App\Security\OAuth2Authenticator;
use OpenIDConnectClient\OpenIDConnectProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class OAuth2AuthenticatorTest.
 *
 * @covers \App\Security\OAuth2Authenticator
 */
class OAuth2AuthenticatorTest extends KernelTestCase
{
    /**
     * @var OAuth2Authenticator
     */
    protected $oAuth2Authenticator;

    /**
     * @var OpenIDConnectProvider
     */
    protected $provider;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        $this->provider = self::$container->get(OpenIDConnectProvider::class);
        $this->oAuth2Authenticator = new OAuth2Authenticator($this->provider);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->oAuth2Authenticator);
        unset($this->provider);
    }

    public function testSupportsRememberMe(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testSupports(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testStart(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testGetCredentials(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testGetUser(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testCheckCredentials(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testOnAuthenticationFailure(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testOnAuthenticationSuccess(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }
}
