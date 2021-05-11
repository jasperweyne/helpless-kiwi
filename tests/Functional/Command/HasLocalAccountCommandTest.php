<?php

namespace Tests\Functional\Command;

use App\Entity\Security\LocalAccount;
use Doctrine\ORM\EntityManagerInterface;
use Tests\Helper\AuthWebTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class HasLocalAccountCommandTest.
 *
 * @covers \App\Command\HasLocalAccountCommand
 */
class HasLocalAccountCommandTest extends AuthWebTestCase
{
    /**
     * @var EntityManagerInterface
     */
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

    public function testExecute()
    {
        // Arrange
        $application = new Application($this->client->getKernel());

        // Act
        $command = $application->find('app:has-account');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);
        
        // Assert
        $output = $commandTester->getDisplay();
        $account = $this->em->getRepository(LocalAccount::class)->findAll();
        $this->assertEquals(count($account) > 0 ? '1' : '0', trim($output));
    }
}
