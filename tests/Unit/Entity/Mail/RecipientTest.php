<?php

namespace Tests\Unit\Entity\Mail;

use App\Entity\Mail\Mail;
use App\Entity\Mail\Recipient;
use App\Entity\Security\LocalAccount;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class RecipientTest.
 *
 * @covers \App\Entity\Mail\Recipient
 *
 * @group entities
 */
class RecipientTest extends KernelTestCase
{
    protected Recipient $recipient;

    /** {@inheritdoc} */
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        /* @todo Correctly instantiate tested object to use it. */
        $this->recipient = new Recipient();
    }

    /** {@inheritdoc} */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->recipient);
    }

    public function testGetId(): void
    {
        $expected = '42';
        $property = (new \ReflectionClass(Recipient::class))
            ->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($this->recipient, $expected);
        self::assertSame($expected, $this->recipient->getId());
    }

    public function testGetPerson(): void
    {
        $expected = new LocalAccount();
        $expected->setEmail('john@doe.eyes');
        $property = (new \ReflectionClass(Recipient::class))
            ->getProperty('person');
        $property->setAccessible(true);
        $property->setValue($this->recipient, $expected);
        self::assertSame($expected, $this->recipient->getPerson());
    }

    public function testSetPerson(): void
    {
        $expected = new LocalAccount();
        $expected->setEmail('john@doe.eyes');
        $property = (new \ReflectionClass(Recipient::class))
            ->getProperty('person');
        $property->setAccessible(true);
        $this->recipient->setPerson($expected);
        self::assertSame($expected, $this->recipient->getPerson());
    }

    public function testGetMail(): void
    {
        $expected = new Mail();
        $property = (new \ReflectionClass(Recipient::class))
            ->getProperty('mail');
        $property->setAccessible(true);
        $property->setValue($this->recipient, $expected);
        self::assertSame($expected, $this->recipient->getMail());
    }

    public function testSetMail(): void
    {
        $expected = new Mail();
        $property = (new \ReflectionClass(Recipient::class))
            ->getProperty('mail');
        $property->setAccessible(true);
        $this->recipient->setMail($expected);
        self::assertSame($expected, $property->getValue($this->recipient));
    }
}
