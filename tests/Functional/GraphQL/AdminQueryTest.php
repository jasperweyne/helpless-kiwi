<?php

namespace Tests\Functional\GraphQL;

use App\Tests\AuthWebTestCase;
use App\Tests\Database\Activity\ActivityFixture;
use App\Tests\Database\Activity\PriceOptionFixture;
use App\Tests\Database\Activity\RegistrationFixture;
use App\Tests\Database\Group\GroupFixture;
use App\Tests\Database\Security\LocalAccountFixture;

/**
 * Class AdminQueryTest.
 *
 * @covers \App\GraphQL\AdminQuery
 */
class AdminQueryTest extends AuthWebTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->login();

        $this->loadFixtures([
            LocalAccountFixture::class,
            PriceOptionFixture::class,
            ActivityFixture::class,
            RegistrationFixture::class,
            GroupFixture::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        $this->logout();
        parent::tearDown();
    }

    public function testActivities(): void
    {
        // Arrange
        $query = <<<GRAPHQL
{
    admin {
        activities {
            name
        }
    }
}
GRAPHQL;

        // Act
        $data = QueryTest::graphqlQuery($this->client, $query);

        // Assert
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertArrayNotHasKey('errors', $data);
        $this->assertTrue(isset($data['data']['admin']['activities']));
        $this->assertCount(1, $data['data']['admin']['activities']);
    }

    public function testGroups(): void
    {
        // Arrange
        $query = <<<GRAPHQL
{
    admin {
        groups {
            name
        }
    }
}
GRAPHQL;

        // Act
        $data = QueryTest::graphqlQuery($this->client, $query);

        // Assert
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertArrayNotHasKey('errors', $data);
        $this->assertTrue(isset($data['data']['admin']['groups']));
        $this->assertCount(1, $data['data']['admin']['groups']);
    }

    public function testUsers(): void
    {
        // Arrange
        $query = <<<GRAPHQL
{
    admin {
        users {
            email
        }
    }
}
GRAPHQL;

        // Act
        $data = QueryTest::graphqlQuery($this->client, $query);

        // Assert
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertArrayNotHasKey('errors', $data);
        $this->assertTrue(isset($data['data']['admin']['users']));
        $this->assertCount(1, $data['data']['admin']['users']);
    }
}
