<?php

namespace Tests\Functional\GraphQL;

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

        $this->loadFixtures([
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
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertArrayNotHasKey('errors', $data);
        $this->assertTrue(isset($data['data']['current']));
        $this->assertCount(1, $data['data']['current']);
        $this->assertNotEmpty($data['data']['current'][0]['registrations']);
        $this->assertNull($data['data']['current'][0]['registrations'][0]['person']['givenName']);
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
        $this->login();
        $data = self::graphqlQuery($this->client, $query);
        $this->logout();

        // Assert
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertArrayNotHasKey('errors', $data);
        $this->assertTrue(isset($data['data']['current']));
        $this->assertCount(1, $data['data']['current']);
        $this->assertNotEmpty($data['data']['current'][0]['registrations']);
        $this->assertNotEmpty(isset($data['data']['current'][0]['registrations'][0]['person']['givenName']));
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
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertArrayNotHasKey('errors', $data);
        $this->assertNull($data['data']['user']);
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
        $this->login();
        $data = self::graphqlQuery($this->client, $query);
        $this->logout();

        // Assert
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertArrayNotHasKey('errors', $data);
        $this->assertTrue(isset($data['data']['user']['email']));
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
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertArrayNotHasKey('errors', $data);
        $this->assertNull($data['data']['activities']);
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
        $this->login();
        $data = self::graphqlQuery($this->client, $query);
        $this->logout();

        // Assert
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertArrayNotHasKey('errors', $data);
        $this->assertTrue(isset($data['data']['activities']));
        $this->assertCount(1, $data['data']['activities']);
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
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertArrayNotHasKey('errors', $data);
        $this->assertNull($data['data']['groups']);
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
        $this->login();
        $data = self::graphqlQuery($this->client, $query);
        $this->logout();

        // Assert
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertArrayNotHasKey('errors', $data);
        $this->assertTrue(isset($data['data']['groups']));
        $this->assertCount(1, $data['data']['groups']);
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
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertArrayNotHasKey('errors', $data);
        $this->assertNull($data['data']['users']);
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
        $this->login();
        $data = self::graphqlQuery($this->client, $query);
        $this->logout();

        // Assert
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertArrayNotHasKey('errors', $data);
        $this->assertTrue(isset($data['data']['users']));
        $this->assertCount(1, $data['data']['users']);
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
