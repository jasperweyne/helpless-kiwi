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
 * Class RemoveTrustedClientCommandTest.
 *
 * @covers \App\Command\Token\RemoveTrustedClientCommand
 */
class RemoveTrustedClientCommandTest extends AuthWebTestCase
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

    public function testExecute(): void
    {
        // Arrange
        $application = new Application($this->client->getKernel());

        // Act
        $client = $this->em->getRepository(TrustedClient::class)->findAll()[0];
        $command = $application->find('token:client:remove');
        $commandTester = new CommandTester($command);
        $exit = $commandTester->execute(['name' => $client->id]);

        $output = $commandTester->getDisplay();
        $client = $this->em->getRepository(TrustedClient::class)->find($client->id);

        // Assert
        self::assertEquals($exit, Command::SUCCESS);
        self::assertStringContainsString('removed', $output);
        self::assertNull($client);
    }

    public function testExecuteUnknown(): void
    {
        // Arrange
        $application = new Application($this->client->getKernel());

        // Act
        $command = $application->find('token:client:remove');
        $commandTester = new CommandTester($command);
        $exit = $commandTester->execute(['name' => 'unknown']);
        $output = $commandTester->getDisplay();

        // Assert
        self::assertEquals($exit, Command::FAILURE);
        self::assertStringContainsString('doesn\'t exist', $output);
    }
}
