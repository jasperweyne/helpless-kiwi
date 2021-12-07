<?php

namespace Tests\Functional\GraphQL;

use App\Controller\Activity\ActivityController;
use App\Tests\AuthWebTestCase;
use App\Tests\Database\Activity\ActivityFixture;
use App\Tests\Database\Activity\PriceOptionFixture;
use App\Tests\Database\Activity\RegistrationFixture;
use App\Tests\Database\Security\LocalAccountFixture;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

/**
 * Class QueryTest.
 *
 * @covers \App\GraphQL\Query
 */
class QueryTest extends AuthWebTestCase
{
    /**
     * @var ActivityController
     */
    protected $activityController;

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

        $this->em = self::$container->get(EntityManagerInterface::class);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->activityController);
    }

    public function testActivities(): void
    {
        // Arrange
        $query = <<<GRAPHQL
{
    activities {
        id
    }
}
GRAPHQL;

        // Act
        $data = self::graphqlQuery($this->client, $query);

        // Assert
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertArrayNotHasKey('errors', $data);
        $this->assertTrue(isset($data['data']['activities']));
        $this->assertCount(1, $data['data']['activities']);
    }

    public function testAdmin(): void
    {
        // Arrange
        $query = <<<GRAPHQL
{
    admin {
        activities {
            id
        }
    }
}
GRAPHQL;

        // Act
        $data = self::graphqlQuery($this->client, $query);

        // Assert
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertTrue(isset($data['data']['admin']));
        $this->assertArrayHasKey('activities', $data['data']['admin']);
        $this->assertNull($data['data']['admin']['activities']);

        $this->assertTrue(isset($data['extensions']['warnings']));
        $this->assertCount(1, $data['extensions']['warnings']);
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
