<?php

namespace Tests\Unit\Entity\Mail;

use App\Entity\Mail\Mail;
use App\Entity\Mail\Recipient;
use ReflectionClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class RecipientTest.
 *
 * @covers \App\Entity\Mail\Recipient
 */
class RecipientTest extends KernelTestCase
{
    /**
     * @var Recipient
     */
    protected $recipient;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        /** @todo Correctly instantiate tested object to use it. */
        $this->recipient = new Recipient();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->recipient);
    }

    public function testGetId(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(Recipient::class))
            ->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($this->recipient, $expected);
        $this->assertSame($expected, $this->recipient->getId());
    }

    public function testGetPersonId(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testSetPersonId(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testGetMail(): void
    {
        $expected = new Mail();
        $property = (new ReflectionClass(Recipient::class))
            ->getProperty('mail');
        $property->setAccessible(true);
        $property->setValue($this->recipient, $expected);
        $this->assertSame($expected, $this->recipient->getMail());
    }

    public function testSetMail(): void
    {
        $expected = new Mail();
        $property = (new ReflectionClass(Recipient::class))
            ->getProperty('mail');
        $property->setAccessible(true);
        $this->recipient->setMail($expected);
        $this->assertSame($expected, $property->getValue($this->recipient));
    }
}
