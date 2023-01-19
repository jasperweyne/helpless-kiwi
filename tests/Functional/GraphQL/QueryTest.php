<?php

namespace Tests\Functional\GraphQL;

use App\Entity\Security\LocalAccount;
use App\Tests\AuthWebTestCase;
use App\Tests\Database\Activity\ActivityFixture;
use App\Tests\Database\Activity\PriceOptionFixture;
use App\Tests\Database\Activity\RegistrationFixture;
use App\Tests\Database\Security\LocalAccountFixture;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

/**
 * Class QueryTest.
 *
 * @covers \App\GraphQL\Query
 */
class QueryTest extends AuthWebTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->databaseTool->loadFixtures([
            LocalAccountFixture::class,
            PriceOptionFixture::class,
            ActivityFixture::class,
            RegistrationFixture::class,
        ]);
    }

    public function testCurrentAnonymous(): void
    {
        // Arrange
        $query = <<<GRAPHQL
{
    current {
        name
        registrations {
            person {
                givenName
            }
        }
    }
}
GRAPHQL;

        // Act
        $data = self::graphqlQuery($this->client, $query);

        // Assert
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        self::assertArrayNotHasKey('errors', $data);
        self::assertTrue(isset($data['data']['current']));
        self::assertCount(1, $data['data']['current']);
        self::assertNotEmpty($data['data']['current'][0]['registrations']);
        self::assertNull($data['data']['current'][0]['registrations'][0]['person']['givenName']);
    }

    public function testCurrent(): void
    {
        // Arrange
        $query = <<<GRAPHQL
{
    current {
        name
        registrations {
            person {
                givenName
            }
        }
    }
}
GRAPHQL;

        // Act
        $this->client->loginUser($this->user());
        $data = self::graphqlQuery($this->client, $query);

        // Assert
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        self::assertArrayNotHasKey('errors', $data);
        self::assertTrue(isset($data['data']['current']));
        self::assertCount(1, $data['data']['current']);
        self::assertNotEmpty($data['data']['current'][0]['registrations']);
        self::assertNotEmpty(isset($data['data']['current'][0]['registrations'][0]['person']['givenName']));
    }

    public function testUserLoggedOut(): void
    {
        // Arrange
        $query = <<<GRAPHQL
{
    user {
        email
    }
}
GRAPHQL;

        // Act
        $data = self::graphqlQuery($this->client, $query);

        // Assert
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        self::assertArrayNotHasKey('errors', $data);
        self::assertNull($data['data']['user']);
    }

    public function testUserLoggedIn(): void
    {
        // Arrange
        $query = <<<GRAPHQL
{
    user {
        email
    }
}
GRAPHQL;

        // Act
        $this->client->loginUser($this->user());
        $data = self::graphqlQuery($this->client, $query);

        // Assert
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        self::assertArrayNotHasKey('errors', $data);
        self::assertTrue(isset($data['data']['user']['email']));
    }

    public function testActivitiesAnonymous(): void
    {
        // Arrange
        $query = <<<GRAPHQL
{
    activities {
        name
    }
}
GRAPHQL;

        // Act
        $data = self::graphqlQuery($this->client, $query);

        // Assert
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        self::assertArrayNotHasKey('errors', $data);
        self::assertNull($data['data']['activities']);
    }

    public function testActivities(): void
    {
        // Arrange
        $query = <<<GRAPHQL
{
    activities {
        name
    }
}
GRAPHQL;

        // Act
        $this->client->loginUser($this->user());
        $data = self::graphqlQuery($this->client, $query);

        // Assert
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        self::assertArrayNotHasKey('errors', $data);
        self::assertTrue(isset($data['data']['activities']));
        self::assertCount(1, $data['data']['activities']);
    }

    public function testGroupsAnonymous(): void
    {
        // Arrange
        $query = <<<GRAPHQL
{
    groups {
        name
    }
}
GRAPHQL;

        // Act
        $data = self::graphqlQuery($this->client, $query);

        // Assert
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        self::assertArrayNotHasKey('errors', $data);
        self::assertNull($data['data']['groups']);
    }

    public function testGroups(): void
    {
        // Arrange
        $query = <<<GRAPHQL
{
    groups {
        name
    }
}
GRAPHQL;

        // Act
        $this->client->loginUser($this->user());
        $data = self::graphqlQuery($this->client, $query);

        // Assert
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        self::assertArrayNotHasKey('errors', $data);
        self::assertTrue(isset($data['data']['groups']));
        self::assertCount(2, $data['data']['groups']);
    }

    public function testUsersAnonymous(): void
    {
        // Arrange
        $query = <<<GRAPHQL
{
    users {
        email
    }
}
GRAPHQL;

        // Act
        $data = self::graphqlQuery($this->client, $query);

        // Assert
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        self::assertArrayNotHasKey('errors', $data);
        self::assertNull($data['data']['users']);
    }

    public function testUsersAsAdmin(): void
    {
        // Arrange
        $query = <<<GRAPHQL
{
    users {
        email
    }
}
GRAPHQL;

        // Act
        $this->client->loginUser($this->user());
        $data = self::graphqlQuery($this->client, $query);

        // Assert
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        self::assertArrayNotHasKey('errors', $data);
        self::assertTrue(isset($data['data']['users']));
        self::assertCount(1, $data['data']['users']);
    }

    public static function graphqlQuery(KernelBrowser $client, string $query, ?string $operation = null, ?array $variables = null): array
    {
        // setup request data
        $params = [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
        ];

        $request = json_encode([
            'query' => $query,
            'variables' => $variables,
            'operationName' => $operation,
        ]);

        // perform request
        $client->request('POST', '/api/graphql/', [], [], $params, $request);

        return json_decode($client->getResponse()->getContent(), true);
    }
}
