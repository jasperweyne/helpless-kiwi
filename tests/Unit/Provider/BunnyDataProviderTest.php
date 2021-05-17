<?php

namespace Tests\Unit\Provider;

use App\Provider\BunnyDataProvider;
use App\Security\OAuth2UserProvider;
use Doctrine\ORM\EntityManagerInterface;
use OpenIDConnectClient\OpenIDConnectProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class BunnyDataProviderTest.
 *
 * @covers \App\Provider\BunnyDataProvider
 */
class BunnyDataProviderTest extends KernelTestCase
{
    /**
     * @var BunnyDataProvider
     */
    protected $bunnyDataProvider;

    /**
     * @var OpenIDConnectProvider
     */
    protected $provider;

    /**
     * @var TokenStorageInterface
     */
    protected $tokens;

    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var OAuth2UserProvider
     */
    protected $userProvider;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        $this->provider = self::$container->get(OpenIDConnectProvider::class);
        $this->tokens = self::$container->get(TokenStorageInterface::class);
        $this->em = self::$container->get(EntityManagerInterface::class);
        $this->userProvider = self::$container->get(OAuth2UserProvider::class);
        $this->bunnyDataProvider = new BunnyDataProvider($this->provider, $this->tokens, $this->em, $this->userProvider);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->bunnyDataProvider);
        unset($this->provider);
        unset($this->tokens);
        unset($this->em);
        unset($this->userProvider);
    }

    public function testGetAddress(): void
    {
        /* @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testGetRequest(): void
    {
        /* @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testFindPerson(): void
    {
        /* @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testFindPersons(): void
    {
        /* @todo This test is incomplete. */
        $this->markTestIncomplete();
    }
}
