<?php

namespace Tests\Unit\Entity\Mail;

use App\Entity\Mail\Mail;
use App\Entity\Security\LocalAccount;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use ReflectionClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class MailTest.
 *
 * @covers \App\Entity\Mail\Mail
 */
class MailTest extends KernelTestCase
{
    /**
     * @var Mail
     */
    protected $mail;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        /* @todo Correctly instantiate tested object to use it. */
        $this->mail = new Mail();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->mail);
    }

    public function testGetId(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(Mail::class))
            ->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($this->mail, $expected);
        $this->assertSame($expected, $this->mail->getId());
    }

    public function testGetTitle(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(Mail::class))
            ->getProperty('title');
        $property->setAccessible(true);
        $property->setValue($this->mail, $expected);
        $this->assertSame($expected, $this->mail->getTitle());
    }

    public function testSetTitle(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(Mail::class))
            ->getProperty('title');
        $property->setAccessible(true);
        $this->mail->setTitle($expected);
        $this->assertSame($expected, $property->getValue($this->mail));
    }

    public function testGetContent(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(Mail::class))
            ->getProperty('content');
        $property->setAccessible(true);
        $property->setValue($this->mail, $expected);
        $this->assertSame($expected, $this->mail->getContent());
    }

    public function testSetContent(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(Mail::class))
            ->getProperty('content');
        $property->setAccessible(true);
        $this->mail->setContent($expected);
        $this->assertSame($expected, $property->getValue($this->mail));
    }

    public function testGetPerson(): void
    {
        $expected = 'John Doe';
        $property = (new ReflectionClass(Mail::class))
            ->getProperty('person');
        $property->setAccessible(true);
        $property->setValue($this->mail, $expected);
        $this->assertSame($expected, $property->getValue($this->mail));
    }

    public function testSetPerson(): void
    {
        $expected = new LocalAccount();
        $expected->setEmail('john@doe.eyes');
        $property = (new ReflectionClass(Mail::class))
            ->getProperty('person');
        $property->setAccessible(true);
        $this->mail->setPerson($expected);
        $this->assertSame($expected, $this->mail->getPerson());
    }

    public function testGetRecipients(): void
    {
        $expected = new ArrayCollection();
        $property = (new ReflectionClass(Mail::class))
            ->getProperty('recipients');
        $property->setAccessible(true);
        $property->setValue($this->mail, $expected);
        $this->assertSame($expected, $this->mail->getRecipients());
    }

    public function testAddRecipient(): void
    {
        /* @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testRemoveRecipient(): void
    {
        /* @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testGetSender(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(Mail::class))
            ->getProperty('sender');
        $property->setAccessible(true);
        $property->setValue($this->mail, $expected);
        $this->assertSame($expected, $this->mail->getSender());
    }

    public function testSetSender(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(Mail::class))
            ->getProperty('sender');
        $property->setAccessible(true);
        $this->mail->setSender($expected);
        $this->assertSame($expected, $property->getValue($this->mail));
    }

    public function testGetSentAt(): void
    {
        $expected = new DateTime();
        $property = (new ReflectionClass(Mail::class))
            ->getProperty('sentAt');
        $property->setAccessible(true);
        $property->setValue($this->mail, $expected);
        $this->assertSame($expected, $this->mail->getSentAt());
    }

    public function testSetSentAt(): void
    {
        $expected = new DateTime();
        $property = (new ReflectionClass(Mail::class))
            ->getProperty('sentAt');
        $property->setAccessible(true);
        $this->mail->setSentAt($expected);
        $this->assertSame($expected, $property->getValue($this->mail));
    }
}
