<?php

namespace Tests\Unit\EventSubscriber;

use App\Entity\Security\LocalAccount;
use App\Event\Security\CreateAccountsEvent;
use App\Event\Security\RemoveAccountsEvent;
use App\EventSubscriber\AccountSubscriber;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class AccountSubscriberTest.
 *
 * @covers \App\EventSubscriber\AccountSubscriber
 */
final class AccountSubscriberTest extends KernelTestCase
{
    private AccountSubscriber $accountSubscriber;
    private EntityManagerInterface&MockObject $em;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->accountSubscriber = new AccountSubscriber($this->em);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->accountSubscriber);
        unset($this->em);
    }

    public function testGetSubscribedEvents(): void
    {
        $subscribedEvents = AccountSubscriber::getSubscribedEvents();
        // Extract all method names
        $handlers = [];
        foreach ($subscribedEvents as $value) {
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

        // Check if all methods exist
        foreach ($handlers as $handler) {
            self::assertTrue(
                method_exists($this->accountSubscriber, $handler),
                "Method $handler does not exist"
            );
        }
    }

    public function testPersistCreateAccounts(): void
    {
        $account1 = self::createMock(LocalAccount::class);
        $account2 = self::createMock(LocalAccount::class);

        /** @var CreateAccountsEvent&MockObject */
        $event = self::getMockBuilder(CreateAccountsEvent::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Set the value of the "accounts" property using reflection
        $reflectionProperty = new \ReflectionProperty(CreateAccountsEvent::class, 'accounts');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($event, [$account1, $account2]);

        $this->em
            ->expects($this::exactly(2))
            ->method('persist')
            ->withConsecutive([$account1], [$account2]);
        $this->em->expects($this::once())->method('flush');

        $this->accountSubscriber->persistCreateAccounts($event);
    }

    public function testRemoveCreateAccounts(): void
    {
        $account1 = self::createMock(LocalAccount::class);
        $account2 = self::createMock(LocalAccount::class);
        $event = self::createMock(RemoveAccountsEvent::class);
        $event->accounts = [$account1, $account2];

        // Expect the remove method to be called on the entity manager for each account
        $this->em
            ->expects($this::exactly(2))
            ->method('remove')
            ->withConsecutive([$account1], [$account2]);

        // Expect the flush method to be called on the entity manager once
        $this->em->expects(self::once())->method('flush');

        // Call the method under test
        $this->accountSubscriber->persistRemoveAccounts($event);
    }
}
