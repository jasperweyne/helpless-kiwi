<?php

namespace Tests\Unit\Entity\Mail;

use App\Entity\Mail\Mail;
use App\Entity\Mail\Recipient;
use App\Entity\Security\LocalAccount;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class MailTest.
 *
 * @covers \App\Entity\Mail\Mail
 *
 * @group entities
 */
class MailTest extends KernelTestCase
{
    protected Mail $mail;

    /** {@inheritdoc} */
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        /* @todo Correctly instantiate tested object to use it. */
        $this->mail = new Mail();
    }

    /** {@inheritdoc} */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->mail);
    }

    public function testGetId(): void
    {
        $expected = '42';
        $property = (new \ReflectionClass(Mail::class))
            ->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($this->mail, $expected);
        self::assertSame($expected, $this->mail->getId());
    }

    public function testSetId(): void
    {
        $expected = '42';
        $property = (new \ReflectionClass(Mail::class))
            ->getProperty('id');
        $property->setAccessible(true);
        $this->mail->setId($expected);
        self::assertSame($expected, $property->getValue($this->mail));
    }

    public function testGetTitle(): void
    {
        $expected = '42';
        $property = (new \ReflectionClass(Mail::class))
            ->getProperty('title');
        $property->setAccessible(true);
        $property->setValue($this->mail, $expected);
        self::assertSame($expected, $this->mail->getTitle());
    }

    public function testSetTitle(): void
    {
        $expected = '42';
        $property = (new \ReflectionClass(Mail::class))
            ->getProperty('title');
        $property->setAccessible(true);
        $this->mail->setTitle($expected);
        self::assertSame($expected, $property->getValue($this->mail));
    }

    public function testGetContent(): void
    {
        $expected = '42';
        $property = (new \ReflectionClass(Mail::class))
            ->getProperty('content');
        $property->setAccessible(true);
        $property->setValue($this->mail, $expected);
        self::assertSame($expected, $this->mail->getContent());
    }

    public function testSetContent(): void
    {
        $expected = '42';
        $property = (new \ReflectionClass(Mail::class))
            ->getProperty('content');
        $property->setAccessible(true);
        $this->mail->setContent($expected);
        self::assertSame($expected, $property->getValue($this->mail));
    }

    public function testGetPerson(): void
    {
        $expected = new LocalAccount();
        $expected->setEmail('john@doe.eyes');
        $property = (new \ReflectionClass(Mail::class))
            ->getProperty('person');
        $property->setAccessible(true);
        $property->setValue($this->mail, $expected);
        self::assertSame($expected, $this->mail->getPerson());
    }

    public function testSetPerson(): void
    {
        $expected = new LocalAccount();
        $expected->setEmail('john@doe.eyes');
        $property = (new \ReflectionClass(Mail::class))
            ->getProperty('person');
        $property->setAccessible(true);
        $this->mail->setPerson($expected);
        self::assertSame($expected, $this->mail->getPerson());
    }

    public function testGetRecipients(): void
    {
        $expected = new ArrayCollection();
        $property = (new \ReflectionClass(Mail::class))
            ->getProperty('recipients');
        $property->setAccessible(true);
        $property->setValue($this->mail, $expected);
        self::assertSame($expected, $this->mail->getRecipients());
    }

    public function testAddRecipient(): void
    {
        /* @todo This test is incomplete. */
        self::markTestIncomplete();
    }

    public function testRemoveRecipient(): void
    {
        $expected = new ArrayCollection();
        $recipient = new Recipient();
        $expected->add($recipient);
        $property = (new \ReflectionClass(Mail::class))
            ->getProperty('recipients');
        $property->setAccessible(true);
        $property->setValue($this->mail, $expected);

        $this->mail->removeRecipient($recipient);
        self::assertNotSame($recipient, $property->getValue($this->mail));
    }

    public function testGetSender(): void
    {
        $expected = '42';
        $property = (new \ReflectionClass(Mail::class))
            ->getProperty('sender');
        $property->setAccessible(true);
        $property->setValue($this->mail, $expected);
        self::assertSame($expected, $this->mail->getSender());
    }

    public function testSetSender(): void
    {
        $expected = '42';
        $property = (new \ReflectionClass(Mail::class))
            ->getProperty('sender');
        $property->setAccessible(true);
        $this->mail->setSender($expected);
        self::assertSame($expected, $property->getValue($this->mail));
    }

    public function testGetSentAt(): void
    {
        $expected = $this->createMock(\DateTime::class);
        $property = (new \ReflectionClass(Mail::class))
            ->getProperty('sentAt');
        $property->setAccessible(true);
        $property->setValue($this->mail, $expected);
        self::assertSame($expected, $this->mail->getSentAt());
    }

    public function testSetSentAt(): void
    {
        $expected = new \DateTime();
        $property = (new \ReflectionClass(Mail::class))
            ->getProperty('sentAt');
        $property->setAccessible(true);
        $this->mail->setSentAt($expected);
        self::assertSame($expected, $property->getValue($this->mail));
    }
}
