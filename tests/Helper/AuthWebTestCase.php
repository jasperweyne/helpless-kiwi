<?php

namespace Tests\Helper;

use App\DataFixtures\Security\LocalAccountFixture;
use App\Entity\Security\LocalAccount;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Guard\Token\PostAuthenticationGuardToken;

/**
 * Class RegistrationControllerTest.
 *
 * @covers \App\Controller\Admin\RegistrationController
 */
class AuthWebTestCase extends WebTestCase
{
    use FixturesTrait;

    /**
     * @var client
     */
    protected $client;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->client);
    }

    protected function login(): void
    {
        $this->loadFixtures([
            LocalAccountFixture::class,
        ]);

        $session = $this->client->getContainer()->get('session');

        $firewallName = 'main';
        $firewallContext = 'main';

        $user = new LocalAccount();
        $user->setEmail(LocalAccountFixture::USERNAME);
        $token = new PostAuthenticationGuardToken($user, $firewallName, ['ROLE_ADMIN']);
        $session->set('_security_'.$firewallContext, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
    }
}