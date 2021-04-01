<?php

namespace Tests\Helper;

use Tests\Helper\Database\Security\LocalAccountFixture;
use App\Entity\Security\LocalAccount;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Guard\Token\PostAuthenticationGuardToken;

/**
 * Extends the WebTestCase class with support for logging in and fixtures.
 */
class AuthWebTestCase extends WebTestCase
{
    use FixturesTrait;

    /**
     * @var \Symfony\Bundle\FrameworkBundle\KernelBrowser
     */
    protected $client;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();
        $this->client->followRedirects(true);
        
        // Get all database tables
        $em = self::$container->get(EntityManagerInterface::class);
        $cmf = $em->getMetadataFactory();
        $classes = $cmf->getAllMetadata();

        // Write all tables to database
        $schema = new SchemaTool($em);
        $schema->createSchema($classes);
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
