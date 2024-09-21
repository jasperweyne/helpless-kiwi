<?php

namespace App\Tests;

use App\Entity\Security\LocalAccount;
use Doctrine\ORM\EntityManagerInterface;
use Hautelook\AliceBundle\PhpUnit\RecreateDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Exception\SessionNotFoundException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Extends the WebTestCase class with support for logging in and fixtures.
 */
class AuthWebTestCase extends WebTestCase
{
    use RecreateDatabaseTrait;

    /**
     * @var \Symfony\Bundle\FrameworkBundle\KernelBrowser
     */
    protected $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();
        $this->client->followRedirects(true);
    }

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
            ->setEmail('admin@kiwi.nl')
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
