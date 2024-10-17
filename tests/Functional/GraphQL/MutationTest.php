<?php

namespace Tests\Functional\GraphQL;

use App\Entity\Security\ApiToken;
use App\Entity\Security\LocalAccount;
use App\Entity\Security\TrustedClient;
use App\Tests\AuthWebTestCase;
use App\Tests\Database\Security\TrustedClientFixture;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;

/**
 * Class MutationTest.
 *
 * @covers \App\GraphQL\Mutation
 */
class MutationTest extends AuthWebTestCase
{
    protected EntityManagerInterface $em;

    protected PasswordHasherInterface $hasher;

    protected function setUp(): void
    {
        parent::setUp();

        $factory = self::getContainer()->get(PasswordHasherFactoryInterface::class);
        $this->hasher = $factory->getPasswordHasher(TrustedClient::class);

        $this->em = self::getContainer()->get(EntityManagerInterface::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->em);
    }

    public function testLogin(): void
    {
        // Arrange
        $user = 'admin@kiwi.nl';
        $pass = 'root';
        $id = 'client';
        $secret = TrustedClientFixture::SECRET;

        $query = <<<GRAPHQL
        mutation {
            login(username: "$user", password: "$pass", clientId: "$id", clientSecret: "$secret")
        }
        GRAPHQL;

        // Act
        $data = QueryTest::graphqlQuery($this->client, $query);

        // Assert
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        self::assertArrayNotHasKey('errors', $data);
        self::assertTrue(isset($data['data']['login']));
        self::assertNotNull($token = $this->em->getRepository(ApiToken::class)->find($data['data']['login']));
        self::assertTrue($token->isValid(new \DateTime('+4 minutes')));
        self::assertSame($user, $token->account->getUserIdentifier());
        self::assertSame($id, $token->client->id);
    }

    public function testLoginInvalidClient(): void
    {
        // Arrange
        $user = 'admin@kiwi.nl';
        $pass = 'root';
        $id = 'client';
        $secret = 'unknown';

        $query = <<<GRAPHQL
        mutation {
            login(username: "$user", password: "$pass", clientId: "$id", clientSecret: "$secret")
        }
        GRAPHQL;

        // Act
        $data = QueryTest::graphqlQuery($this->client, $query);

        // Assert
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        self::assertArrayHasKey('errors', $data);
        self::assertCount(1, $data['errors']);
        self::assertSame('Unknown client', $data['errors'][0]['message']);
        self::assertNull($data['data']['login']);
    }

    public function testLoginWrongPassword(): void
    {
        // Arrange
        $user = 'admin@kiwi.nl';
        $pass = 'wrong';
        $id = 'client';
        $secret = TrustedClientFixture::SECRET;

        $query = <<<GRAPHQL
        mutation {
            login(username: "$user", password: "$pass", clientId: "$id", clientSecret: "$secret")
        }
        GRAPHQL;

        // Act
        $data = QueryTest::graphqlQuery($this->client, $query);

        // Assert
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        self::assertArrayHasKey('errors', $data);
        self::assertCount(1, $data['errors']);
        self::assertSame('Invalid credentials', $data['errors'][0]['message']);
        self::assertNull($data['data']['login']);
    }

    public function testLogout(): void
    {
        // Arrange
        $user = $this->user('admin@kiwi.nl');
        $client = $this->em->find(TrustedClient::class, 'client');
        assert($user instanceof LocalAccount && null !== $client);
        $this->em->persist($token = new ApiToken($user, $client, new \DateTimeImmutable('+5 minutes')));
        $tokenString = $token->token;

        $query = <<<GRAPHQL
        mutation {
            logout(tokenString: "$tokenString")
        }
        GRAPHQL;

        // Act
        $this->client->loginUser($user);
        $data = QueryTest::graphqlQuery($this->client, $query);

        // Assert
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        self::assertArrayNotHasKey('errors', $data);
        self::assertNull($this->em->getRepository(ApiToken::class)->find($tokenString));
    }

    public function testLogoutUnknown(): void
    {
        // Arrange
        $user = $this->user('admin@kiwi.nl');
        assert($user instanceof LocalAccount);

        $query = <<<GRAPHQL
        mutation {
            logout(tokenString: "unknownToken")
        }
        GRAPHQL;

        // Act
        $this->client->loginUser($user);
        $data = QueryTest::graphqlQuery($this->client, $query);

        // Assert
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        self::assertArrayHasKey('errors', $data);
        self::assertCount(1, $data['errors']);
        self::assertSame('Unknown token', $data['errors'][0]['message']);
    }

    public function testLogoutUnauthorized(): void
    {
        // Arrange
        $user = $this->user('admin@kiwi.nl');
        $client = $this->em->find(TrustedClient::class, 'client');
        assert($user instanceof LocalAccount && null !== $client);
        $this->em->persist($token = new ApiToken($user, $client, new \DateTimeImmutable('+5 minutes')));
        $tokenString = $token->token;

        $query = <<<GRAPHQL
        mutation {
            logout(tokenString: "$tokenString")
        }
        GRAPHQL;

        // Act
        $data = QueryTest::graphqlQuery($this->client, $query);

        // Assert
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        self::assertArrayHasKey('errors', $data);
        self::assertCount(1, $data['errors']);
        self::assertStringStartsWith('Not authorized', $data['errors'][0]['message']);
    }
}
