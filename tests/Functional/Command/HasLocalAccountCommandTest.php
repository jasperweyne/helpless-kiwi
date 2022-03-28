<?php

namespace Tests\Functional\Command;

use App\Tests\AuthWebTestCase;
use App\Tests\Database\Security\LocalAccountFixture;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class HasLocalAccountCommandTest.
 *
 * @covers \App\Command\HasLocalAccountCommand
 */
class HasLocalAccountCommandTest extends AuthWebTestCase
{
    /** @var EntityManagerInterface */
    protected $em;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->em = self::$container->get(EntityManagerInterface::class);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->em);
    }

    public function testExecuteEmpty(): void
    {
        // Arrange
        $application = new Application($this->client->getKernel());

        // Act
        $command = $application->find('app:has-account');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        // Assert
        $output = $commandTester->getDisplay();
        $this::assertEquals('0', trim($output));
    }

    public function testExecuteWithFixtures(): void
    {
        // Arrange
        $this->loadFixtures([LocalAccountFixture::class]);
        $application = new Application($this->client->getKernel());

        // Act
        $command = $application->find('app:has-account');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        // Assert
        $output = $commandTester->getDisplay();
        $this::assertEquals('1', trim($output));
    }
}
