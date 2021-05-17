<?php

namespace Tests\Unit\Mail;

use App\Mail\MailService;
use Doctrine\ORM\EntityManagerInterface;
use Swift_Mailer;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class MailServiceTest.
 *
 * @covers \App\Mail\MailService
 */
class MailServiceTest extends KernelTestCase
{
    /**
     * @var MailService
     */
    protected $mailService;

    /**
     * @var Swift_Mailer
     */
    protected $mailer;

    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var ParameterBagInterface
     */
    protected $params;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        $this->mailer = self::$container->get(Swift_Mailer::class);
        $this->em = self::$container->get(EntityManagerInterface::class);
        $this->tokenStorage = self::$container->get(TokenStorageInterface::class);
        $this->params = self::$container->get(ParameterBagInterface::class);
        $this->mailService = new MailService($this->mailer, $this->em, $this->tokenStorage, $this->params);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->mailService);
        unset($this->mailer);
        unset($this->em);
        unset($this->tokenStorage);
        unset($this->params);
    }

    public function testMessage(): void
    {
        /* @todo This test is incomplete. */
        $this->markTestIncomplete();
    }
}
