<?php

namespace Tests\Functional\Command\Token;

use App\Entity\Security\ApiToken;
use App\Tests\AuthWebTestCase;
use App\Tests\Database\Security\LocalAccountFixture;
use App\Tests\Database\Security\TrustedClientFixture;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class GenerateApiTokenCommandTest.
 *
 * @covers \App\Command\Token\GenerateApiTokenCommand
 */
class GenerateApiTokenCommandTest extends AuthWebTestCase
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
        $originalCount = $this->em->getRepository(ApiToken::class)->count([]);

        // Act
        $command = $application->find('token:generate');
        $commandTester = new CommandTester($command);
        $exit = $commandTester->execute([
            'username' => LocalAccountFixture::USERNAME,
            'client' => TrustedClientFixture::ID,
        ]);

        $newCount = $this->em->getRepository(ApiToken::class)->count([]);

        // Assert
        self::assertEquals($exit, Command::SUCCESS);
        self::assertEquals(1, $newCount - $originalCount, "Registration count of activity didn't correctly change after POST request.");
    }

    public function testExecuteUnknownUser(): void
    {
        // Arrange
        $application = new Application($this->client->getKernel());

        // Act
        $command = $application->find('token:generate');
        $commandTester = new CommandTester($command);
        $exit = $commandTester->execute([
            'username' => ($username = 'unknown'),
            'client' => TrustedClientFixture::ID,
        ]);

        $output = $commandTester->getDisplay();

        // Assert
        self::assertEquals($exit, Command::FAILURE);
        self::assertStringContainsString("'{$username}' doesn't exist", $output);
    }

    public function testExecuteUnknownClient(): void
    {
        // Arrange
        $application = new Application($this->client->getKernel());

        // Act
        $command = $application->find('token:generate');
        $commandTester = new CommandTester($command);
        $exit = $commandTester->execute([
            'username' => LocalAccountFixture::USERNAME,
            'client' => ($client = 'unknown'),
        ]);

        $output = $commandTester->getDisplay();

        // Assert
        self::assertEquals($exit, Command::FAILURE);
        self::assertStringContainsString("'{$client}' doesn't exist", $output);
    }
}
