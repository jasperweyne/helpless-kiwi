<?php

namespace Tests\Functional\GraphQL;

use App\Tests\AuthWebTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

/**
 * Class QueryTest.
 *
 * @covers \App\GraphQL\Query
 */
class QueryTest extends AuthWebTestCase
{
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
        self::assertEmpty(array_merge(...array_column($data['data']['current'], 'registrations')));
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
        self::assertNotEmpty(array_merge(...array_column($data['data']['current'], 'registrations')));
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
        self::assertCount(3, $data['data']['activities']);
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
        self::assertCount(5, $data['data']['users']);
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
        self::assertIsString($request);

        // perform request
        $client->request('POST', '/api/graphql/', [], [], $params, $request);

        self::assertIsString($client->getResponse()->getContent());

        return (array) json_decode($client->getResponse()->getContent(), true);
    }
}
