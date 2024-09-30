<?php

namespace Tests\Unit\EventSubscriber;

use App\Entity\Security\LocalAccount;
use App\EventSubscriber\MailSentSubscriber;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\Event\MessageEvent;
use Symfony\Component\Mime\Email;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class MailSentSubscriberTest.
 *
 * @covers \App\EventSubscriber\MailSentSubscriber
 */
class MailSentSubscriberTest extends KernelTestCase
{
    private MailSentSubscriber $mailSentSubscriber;
    private EntityManagerInterface&MockObject $em;
    private TokenStorageInterface&MockObject $tokenStorage;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->em = self::createMock(EntityManagerInterface::class);
        $this->tokenStorage = self::createMock(TokenStorageInterface::class);
        $this->mailSentSubscriber = new MailSentSubscriber(
            $this->em,
            $this->tokenStorage,
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->em);
        unset($this->tokenStorage);
        unset($this->mailSentSubscriber);
    }

    public function testGetSubscribedEvents(): void
    {
        // extract all method names
        $handlers = [];
        foreach (MailSentSubscriber::getSubscribedEvents() as $value) {
            if (is_string($value)) {
                $handlers[] = $value;
            } elseif (is_string($value[0])) {
                $handlers[] = $value[0];
            } else {
                foreach ($value as $nested) {
                    assert(is_array($nested));
                    $handlers[] = (string) $nested[0];
                }
            }
        }

        // check if all methods exist
        foreach ($handlers as $handler) {
            self::assertTrue(
                method_exists($this->mailSentSubscriber, $handler),
                "Method $handler does not exist"
            );
        }
    }

    public function testStoreEmail(): void
    {
        $envelope = $this->createMock(Envelope::class);
        $event = new MessageEvent($msg = new Email(), $envelope, 'test');
        $msg->subject('test');

        /** @var EntityRepository<LocalAccount>&MockObject $accountRepository */
        $accountRepository = $this->createMock(EntityRepository::class);
        $accountRepository->method('findBy')->willReturn([]);

        $this->em->method('getRepository')->willReturn($accountRepository);
        $this->em->expects(self::atLeastOnce())->method('persist');
        $this->em->expects(self::once())->method('flush');

        $this->mailSentSubscriber->storeEmail($event);
    }
}
