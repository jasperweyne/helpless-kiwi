<?php

namespace Tests\Unit\Event\Security;

use App\Entity\Security\LocalAccount;
use App\Event\Security\CreateAccountsEvent;
use PHPUnit\Framework\TestCase;

/**
 * Class CreateAccountsEventTest.
 *
 * @covers \App\Entity\Security\CreateAccountsEvent
 */
class CreateAccountsEventTest extends TestCase
{
    public function testConstructor(): void
    {
        $account1 = $this->createMock(LocalAccount::class);
        $account2 = $this->createMock(LocalAccount::class);

        $event = new CreateAccountsEvent([$account1, $account2]);

        $this::assertSame([$account1, $account2], $event->accounts);
    }
}
