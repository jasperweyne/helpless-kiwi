<?php

namespace Tests\Functional\GraphQL;

use App\Entity\Security\ApiToken;
use App\Entity\Security\LocalAccount;
use App\Entity\Security\TrustedClient;
use App\Tests\AuthWebTestCase;
use App\Tests\Database\Security\LocalAccountFixture;
use App\Tests\Database\Security\TrustedClientFixture;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class MutationTest.
 *
 * @covers \App\GraphQL\Mutation
 */
class MutationTest extends AuthWebTestCase
{
    protected EntityManagerInterface $em;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->em = self::getContainer()->get(EntityManagerInterface::class);
        $this->databaseTool->loadFixtures([
            LocalAccountFixture::class,
            TrustedClientFixture::class
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->em);
    }

    public function testLogin(): void
    {
        // Arrange
        $user = LocalAccountFixture::USERNAME;
        $pass = 'root';
        $secret = TrustedClientFixture::SECRET;

        $query = <<<GRAPHQL
        mutation {
            login(username: "$user", password: "$pass", clientSecret: "$secret")
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
        self::assertSame($secret, $token->client->secret);
    }

    public function testLoginInvalidClient(): void
    {
        // Arrange
        $user = LocalAccountFixture::USERNAME;
        $pass = 'root';
        $secret = 'unknown';

        $query = <<<GRAPHQL
        mutation {
            login(username: "$user", password: "$pass", clientSecret: "$secret")
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
        $user = LocalAccountFixture::USERNAME;
        $pass = 'wrong';
        $secret = TrustedClientFixture::SECRET;

        $query = <<<GRAPHQL
        mutation {
            login(username: "$user", password: "$pass", clientSecret: "$secret")
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
        /* @todo This test is incomplete. */
        self::markTestIncomplete();
    }

    public function testLogoutUnauthorized(): void
    {
        // Arrange
        $user = $this->user(LocalAccountFixture::USERNAME);
        assert($user instanceof LocalAccount);
        $client = $this->em->getPartialReference(TrustedClient::class, TrustedClientFixture::ID);
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
    }
}
