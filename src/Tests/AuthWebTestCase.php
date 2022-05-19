<?php

namespace App\Tests;

use App\Entity\Security\LocalAccount;
use App\Tests\Database\Security\LocalAccountFixture;
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
        /** @var EntityManagerInterface */
        $em = self::$container->get(EntityManagerInterface::class);
        $users = $em->getRepository(LocalAccount::class)->findAll();
        if (empty($users)) {
            throw new \RuntimeException('Tried to login without users in the database. Did you load LocalAccountFixture before running login()?.');
        }

        $session = $this->client->getContainer()->get('session');

        $firewallName = 'main';
        $firewallContext = 'main';

        $user = new LocalAccount();
        $user->setEmail(LocalAccountFixture::USERNAME);
        $token = new PostAuthenticationGuardToken($user, $firewallName, ['ROLE_ADMIN', 'ROLE_USER']);
        $session->set('_security_'.$firewallContext, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
    }

    protected function logout(): void
    {
        $this->client->getContainer()->get('session')->invalidate();
        $this->client->getContainer()->get('security.token_storage')->setToken(null);
    }

    protected function loginNotAdmin(): void
    {
        /** @var EntityManagerInterface */
        $em = self::$container->get(EntityManagerInterface::class);
        $users = $em->getRepository(LocalAccount::class)->findAll();
        if (empty($users)) {
            throw new \RuntimeException('Tried to login without users in the database. Did you load LocalAccountFixture before running login()?.');
        }

        $session = $this->client->getContainer()->get('session');

        $firewallName = 'main';
        $firewallContext = 'main';

        $user = new LocalAccount();
        $user->setEmail(LocalAccountFixture::USERNAME);
        $token = new PostAuthenticationGuardToken($user, $firewallName, ['ROLE_USER']);
        $session->set('_security_'.$firewallContext, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
    }
}
