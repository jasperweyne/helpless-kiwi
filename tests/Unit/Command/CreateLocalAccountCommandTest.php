<?php

namespace Tests\Unit\Command;

use App\Command\CreateLocalAccountCommand;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Class CreateLocalAccountCommandTest.
 *
 * @covers \App\Command\CreateLocalAccountCommand
 */
class CreateLocalAccountCommandTest extends KernelTestCase
{
    /**
     * @var CreateLocalAccountCommand
     */
    protected $createLocalAccountCommand;

    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var UserPasswordEncoderInterface
     */
    protected $passwordEncoder;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        $this->em = self::$container->get(EntityManagerInterface::class);
        $this->passwordEncoder = self::$container->get(UserPasswordEncoderInterface::class);
        $this->createLocalAccountCommand = new CreateLocalAccountCommand($this->em, $this->passwordEncoder);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->createLocalAccountCommand);
        unset($this->em);
        unset($this->passwordEncoder);
    }
}
