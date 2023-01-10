<?php

namespace App\Tests;

use App\Entity\Security\LocalAccount;
use App\Tests\Database\Security\LocalAccountFixture;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Guard\Token\PostAuthenticationGuardToken;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Extends the WebTestCase class with support for logging in and fixtures.
 */
class AuthWebTestCase extends WebTestCase
{
    /**
     * @var \Symfony\Bundle\FrameworkBundle\KernelBrowser
     */
    protected $client;


    /**
     * @var AbstractDatabaseTool
     */
    protected $databaseTool;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();
        $this->client->followRedirects(true);

        // Get all database tables
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $cmf = $em->getMetadataFactory();
        $classes = $cmf->getAllMetadata();

        // Write all tables to database
        $schema = new SchemaTool($em);
        $schema->createSchema($classes);

        // Load database tool
        $this->databaseTool = $this->client->getContainer()->get(DatabaseToolCollection::class)->get();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->client);
    }

    /**
     * @param string[] $roles
     */
    protected function login($roles = ['ROLE_ADMIN']): void
    {
        /** @var EntityManagerInterface */
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $users = $em->getRepository(LocalAccount::class)->findAll();
        if (empty($users)) {
            throw new \RuntimeException('Tried to login without users in the database. Did you load LocalAccountFixture before running login()?.');
        }

        $session = self::getContainer()->get(SessionInterface::class);

        $firewallName = 'main';
        $firewallContext = 'main';

        $user = new LocalAccount();
        $user->setEmail(LocalAccountFixture::USERNAME);
        $user->setRoles($roles);

        $token = new PostAuthenticationGuardToken($user, $firewallName, $user->getRoles());
        $session->set('_security_'.$firewallContext, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
    }

    protected function logout(): void
    {
        self::getContainer()->get(SessionInterface::class)->invalidate();
        self::getContainer()->get('security.token_storage')->setToken(null);
    }
}
