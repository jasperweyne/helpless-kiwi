<?php

namespace Tests\Functional\Command;

use App\Entity\Security\LocalAccount;
use App\Tests\AuthWebTestCase;
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
    protected EntityManagerInterface $em;

    protected function setUp(): void
    {
        parent::setUp();

        $this->em = self::getContainer()->get(EntityManagerInterface::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->em);
    }

    public function testExecuteEmpty(): void
    {
        // Arrange
        $application = new Application($this->client->getKernel());
        $repo = $this->em->getRepository(LocalAccount::class);
        $localAccounts = $repo->findAll();
        foreach ($localAccounts as $localAccount) {
            $this->em->remove($localAccount);
        }
        $this->em->flush();

        // Act
        $command = $application->find('app:has-account');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        // Assert
        $output = $commandTester->getDisplay();
        self::assertEquals('0', trim($output));
    }

    public function testExecuteWithFixtures(): void
    {
        // Arrange
        $application = new Application($this->client->getKernel());

        // Act
        $command = $application->find('app:has-account');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        // Assert
        $output = $commandTester->getDisplay();
        self::assertEquals('1', trim($output));
    }
}
