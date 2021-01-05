<?php

namespace Tests\Unit\Security;

use App\Security\OAuth2UserProvider;
use Doctrine\ORM\EntityManagerInterface;
use OpenIDConnectClient\OpenIDConnectProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class OAuth2UserProviderTest.
 *
 * @covers \App\Security\OAuth2UserProvider
 */
class OAuth2UserProviderTest extends KernelTestCase
{
    /**
     * @var OAuth2UserProvider
     */
    protected $oAuth2UserProvider;

    /**
     * @var OpenIDConnectProvider
     */
    protected $provider;

    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        $this->provider = self::$container->get(OpenIDConnectProvider::class);
        $this->em = self::$container->get(EntityManagerInterface::class);
        $this->oAuth2UserProvider = new OAuth2UserProvider($this->provider, $this->em);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->oAuth2UserProvider);
        unset($this->provider);
        unset($this->em);
    }

    public function testLoadUserByUsername(): void
    {
        /* @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testRefreshUser(): void
    {
        /* @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testSupportsClass(): void
    {
        /* @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testLogout(): void
    {
        /* @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testOnLogoutSuccess(): void
    {
        /* @todo This test is incomplete. */
        $this->markTestIncomplete();
    }
}
