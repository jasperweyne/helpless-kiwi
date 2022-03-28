<?php

namespace Tests\Functional\Command;

use App\Entity\Security\LocalAccount;
use App\Tests\AuthWebTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class CreateLocalAccountCommandTest.
 *
 * @covers \App\Command\CreateLocalAccountCommand
 */
class CreateLocalAccountCommandTest extends AuthWebTestCase
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

    // This feels not nice. But as of now the password menu loops till the
    // heatdeath of the universe. We might need to rewrite it, but this
    // addressed the untested code.
    //
    // As soon as we've done this I'll split this up in 3/4 test cases.
    // But for now it will at least show us where the error resides.
    public function testInteract(): void
    {
        $email = 'user@kiwi.au';
        $commandName = 'app:create-account';
        $application = new Application($this->client->getKernel());
        $foundCommand = $application->find($commandName);
        $tester = new CommandTester($foundCommand);
        $tester->setInputs(['Aussie', '', 'password', 'password', '', 'password', 'passw0rd', 'password', 'password']);
        $statusCode = $tester->execute([
            'command' => $commandName,
            'email' => $email,
        ]);

        $output = $tester->getDisplay();
        $account = $this->em->getRepository(LocalAccount::class)->findBy(['email' => $email]);

        // Assert
        $this::assertContains('login registered!', $output);
        $this::assertEquals(count($account), 1);
        $this::assertEquals(0, $statusCode);
    }

    public function testExecute(): void
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
            'pass' => 'test1234',
        ]);

        $output = $commandTester->getDisplay();
        $account = $this->em->getRepository(LocalAccount::class)->findBy(['email' => $email]);

        // Assert
        $this::assertContains('login registered!', $output);
        $this::assertEquals(count($account), 1);
    }
}
