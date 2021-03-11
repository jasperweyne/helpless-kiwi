<?php

namespace Tests\Unit\EventListener;

use App\EventListener\DoctrineTablePrefixListener;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class DoctrineTablePrefixListenerTest.
 *
 * @covers \App\EventListener\DoctrineTablePrefixListener
 */
class DoctrineTablePrefixListenerTest extends KernelTestCase
{
    /**
     * @var DoctrineTablePrefixListener
     */
    protected $doctrineTablePrefixListener;

    /**
     * @var mixed
     */
    protected $prefix;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        $this->prefix = null;
        $this->doctrineTablePrefixListener = new DoctrineTablePrefixListener($this->prefix);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->doctrineTablePrefixListener);
        unset($this->prefix);
    }

    public function testLoadClassMetadata(): void
    {
        /* @todo This test is incomplete. */
        $this->markTestIncomplete();
    }
}
