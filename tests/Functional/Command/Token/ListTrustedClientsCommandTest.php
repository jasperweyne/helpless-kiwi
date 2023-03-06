<?php

namespace Tests\Functional\Command\Token;

use App\Tests\AuthWebTestCase;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class ListTrustedClientsCommandTest.
 *
 * @covers \App\Command\Token\ListTrustedClientsCommand
 */
class ListTrustedClientsCommandTest extends AuthWebTestCase
{
    /** @var EntityManagerInterface */
    protected $em;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->em = self::getContainer()->get(EntityManagerInterface::class);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->em);
    }

    public function testExecute(): void
    {
        /* @todo This test is incomplete. */
        self::markTestIncomplete();
    }
}
