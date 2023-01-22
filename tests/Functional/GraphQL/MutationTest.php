<?php

namespace Tests\Functional\GraphQL;

use App\Entity\Security\ApiToken;
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

    public function testLogout(): void
    {
        /* @todo This test is incomplete. */
        self::markTestIncomplete();
    }
}
