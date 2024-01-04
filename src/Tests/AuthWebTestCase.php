<?php

namespace App\Tests;

use App\Entity\Security\LocalAccount;
use App\Tests\Database\Security\LocalAccountFixture;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Exception\SessionNotFoundException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\User\UserInterface;

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
     * Retrieve or instantiate a user.
     *
     * @param string[]|string $roles The roles of the instantiated user, or the email of the user in database
     */
    protected function user($roles = ['ROLE_ADMIN']): UserInterface
    {
        if (is_string($roles)) {
            $username = $roles;

            /** @var EntityManagerInterface */
            $em = self::getContainer()->get(EntityManagerInterface::class);
            $user = $em->getRepository(LocalAccount::class)->findOneBy(['email' => $username]);

            if (null === $user) {
                throw new \InvalidArgumentException("User with email '$username' was not found in the test database.");
            }

            return $user;
        }

        $user = new LocalAccount();
        $user
            ->setEmail(LocalAccountFixture::USERNAME)
            ->setRoles($roles)
        ;

        return $user;
    }

    /**
     * @param string[] $roles
     */
    protected function login($roles = ['ROLE_ADMIN']): void
    {
        /** @var EntityManagerInterface */
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $users = $em->getRepository(LocalAccount::class)->findAll();
        if (0 === count($users)) {
            throw new \RuntimeException('Tried to login without users in the database. Did you load LocalAccountFixture before running login()?.');
        }

        $this->client->loginUser($this->user($roles));
    }

    protected function logout(): void
    {
        try {
            self::getContainer()->get(RequestStack::class)->getSession()->invalidate();
        } catch (SessionNotFoundException) {
        }
        self::getContainer()->get('security.token_storage')->setToken(null);
    }
}
