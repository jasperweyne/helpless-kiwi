<?php

namespace Tests\Functional\Command;

use App\Entity\Security\LocalAccount;
use Doctrine\ORM\EntityManagerInterface;
use Tests\Helper\AuthWebTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class CreateLocalAccountCommandTest.
 *
 * @covers \App\Command\CreateLocalAccountCommand
 */
class CreateLocalAccountCommandTest extends AuthWebTestCase
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
        $email = 'test@test.com';

        $command = $application->find('app:create-account');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'email' => $email,
            'name' => 'Test Account',
            'pass' => 'test1234'
        ]);

        $output = $commandTester->getDisplay();
        $account = $this->em->getRepository(LocalAccount::class)->findBy(['email' => $email]);

        // Assert
        $this->assertContains('login registered!', $output);
        $this->assertEquals(count($account), 1);
    }
}
