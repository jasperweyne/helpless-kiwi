<?php

namespace Tests\Functional\Command\Token;

use App\Entity\Security\TrustedClient;
use App\Tests\AuthWebTestCase;
use App\Tests\Database\Security\TrustedClientFixture;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class CreateTrustedClientCommandTest.
 *
 * @covers \App\Command\Token\CreateTrustedClientCommand
 */
class CreateTrustedClientCommandTest extends AuthWebTestCase
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

        // Act
        $clientName = 'testClient';

        $command = $application->find('token:client:create');
        $commandTester = new CommandTester($command);
        $exit = $commandTester->execute(['name' => $clientName]);

        $output = $commandTester->getDisplay();
        $client = $this->em->getRepository(TrustedClient::class)->find($clientName);

        // Assert
        self::assertEquals($exit, Command::SUCCESS);
        self::assertStringContainsString('generated!', $output);
        self::assertNotNull($client);
    }

    public function testExecuteWithDuplicate(): void
    {
        // Arrange
        $application = new Application($this->client->getKernel());

        // Act
        $command = $application->find('token:client:create');
        $commandTester = new CommandTester($command);
        $exit = $commandTester->execute(['name' => TrustedClientFixture::ID]);
        $output = $commandTester->getDisplay();

        // Assert
        self::assertEquals($exit, Command::FAILURE);
        self::assertStringContainsString('already exists', $output);
    }
}
