<?php

namespace Tests\Unit\Event\Security;

use App\Entity\Security\LocalAccount;
use App\Event\Security\RemoveAccountsEvent;
use PHPUnit\Framework\TestCase;

/**
 * Class RemoveAccountsEventTest.
 *
 * @covers \App\Event\Security\RemoveAccountsEvent
 */
class RemoveAccountsEventTest extends TestCase
{
    public function testConstructor(): void
    {
        $account1 = $this->createMock(LocalAccount::class);
        $account2 = $this->createMock(LocalAccount::class);

        $event = new RemoveAccountsEvent([$account1, $account2]);

        $this::assertSame([$account1, $account2], $event->accounts);
    }
}
