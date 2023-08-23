<?php

namespace Tests\Functional\Command\Token;

use App\Entity\Security\ApiToken;
use App\Entity\Security\LocalAccount;
use App\Entity\Security\TrustedClient;
use App\Tests\AuthWebTestCase;
use App\Tests\Database\Security\LocalAccountFixture;
use App\Tests\Database\Security\TrustedClientFixture;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class RevokeApiTokenCommandTest.
 *
 * @covers \App\Command\Token\RevokeApiTokenCommand
 */
class RevokeApiTokenCommandTest extends AuthWebTestCase
{
    /** @var EntityManagerInterface */
    protected $em;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->em = self::getContainer()->get(EntityManagerInterface::class);
        $this->databaseTool->loadFixtures([
            LocalAccountFixture::class,
            TrustedClientFixture::class,
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

    public function testExecute(): void
    {
        // Arrange
        $application = new Application($this->client->getKernel());
        $user = $this->em->getRepository(LocalAccount::class)->findOneBy(['email' => LocalAccountFixture::USERNAME]);
        $client = $this->em->getRepository(TrustedClient::class)->find(TrustedClientFixture::ID);
        assert(null !== $user && null !== $client);
        $this->em->persist($token = new ApiToken($user, $client, new \DateTimeImmutable('+5 minutes')));

        // Act
        $command = $application->find('token:revoke');
        $commandTester = new CommandTester($command);
        $exit = $commandTester->execute(['token' => $token->token]);

        $output = $commandTester->getDisplay();
        $result = $this->em->getRepository(TrustedClient::class)->find($token->token);

        // Assert
        self::assertEquals($exit, Command::SUCCESS);
        self::assertStringContainsString('revoked', $output);
        self::assertNull($result);
    }

    public function testExecuteUnknown(): void
    {
        // Arrange
        $application = new Application($this->client->getKernel());

        // Act
        $command = $application->find('token:revoke');
        $commandTester = new CommandTester($command);
        $exit = $commandTester->execute(['token' => 'unknown']);
        $output = $commandTester->getDisplay();

        // Assert
        self::assertEquals($exit, Command::FAILURE);
        self::assertStringContainsString('doesn\'t exist', $output);
    }
}
