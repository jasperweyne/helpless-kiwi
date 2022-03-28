<?php

namespace Tests\Unit\Entity\Mail;

use App\Entity\Mail\Mail;
use App\Entity\Mail\Recipient;
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
        $this::assertSame($expected, $this->mail->getId());
    }

    public function testGetTitle(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(Mail::class))
            ->getProperty('title');
        $property->setAccessible(true);
        $property->setValue($this->mail, $expected);
        $this::assertSame($expected, $this->mail->getTitle());
    }

    public function testSetTitle(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(Mail::class))
            ->getProperty('title');
        $property->setAccessible(true);
        $this->mail->setTitle($expected);
        $this::assertSame($expected, $property->getValue($this->mail));
    }

    public function testGetContent(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(Mail::class))
            ->getProperty('content');
        $property->setAccessible(true);
        $property->setValue($this->mail, $expected);
        $this::assertSame($expected, $this->mail->getContent());
    }

    public function testSetContent(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(Mail::class))
            ->getProperty('content');
        $property->setAccessible(true);
        $this->mail->setContent($expected);
        $this::assertSame($expected, $property->getValue($this->mail));
    }

    public function testGetPerson(): void
    {
        $expected = new LocalAccount();
        $expected->setEmail('john@doe.eyes');
        $property = (new ReflectionClass(Mail::class))
            ->getProperty('person');
        $property->setAccessible(true);
        $property->setValue($this->mail, $expected);
        $this::assertSame($expected, $this->mail->getPerson());
    }

    public function testSetPerson(): void
    {
        $expected = new LocalAccount();
        $expected->setEmail('john@doe.eyes');
        $property = (new ReflectionClass(Mail::class))
            ->getProperty('person');
        $property->setAccessible(true);
        $this->mail->setPerson($expected);
        $this::assertSame($expected, $this->mail->getPerson());
    }

    public function testGetRecipients(): void
    {
        $expected = $this->createMock(Recipient::class);
        $property = (new ReflectionClass(Mail::class))
            ->getProperty('recipients');
        $property->setAccessible(true);
        $property->setValue($this->mail, $expected);
        $this::assertSame($expected, $this->mail->getRecipients());
    }

    public function testAddRecipient(): void
    {
        $expected = $this->createMock(Recipient::class);
        $property = (new ReflectionClass(Mail::class))
            ->getProperty('recipients');
        $property->setAccessible(true);
        $property->setValue($this->mail, new ArrayCollection());
        $this->mail->addRecipient($expected);
        // todo: I dunno why, this is how it comes back. recipients is an array
        // getValue returns a mixed, so an arraycollection.
        //
        // PHPstan disallows reading a mixed as an array.
        // If you have ideas, please do so <3
        $expectedAsCorrectArray = new ArrayCollection([$expected]);
        $this::assertSame($expectedAsCorrectArray, $property->getValue($this->mail));
    }

    public function testRemoveRecipient(): void
    {
        $expected = new ArrayCollection();
        $recipient = new Recipient();
        $expected->add($recipient);
        $property = (new ReflectionClass(Mail::class))
            ->getProperty('recipients');
        $property->setAccessible(true);
        $property->setValue($this->mail, $expected);

        $this->mail->removeRecipient($recipient);
        $this::assertNotSame($recipient, $property->getValue($this->mail));
    }

    public function testGetSender(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(Mail::class))
            ->getProperty('sender');
        $property->setAccessible(true);
        $property->setValue($this->mail, $expected);
        $this::assertSame($expected, $this->mail->getSender());
    }

    public function testSetSender(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(Mail::class))
            ->getProperty('sender');
        $property->setAccessible(true);
        $this->mail->setSender($expected);
        $this::assertSame($expected, $property->getValue($this->mail));
    }

    public function testGetSentAt(): void
    {
        $expected = $this->createMock(DateTime::class);
        $property = (new ReflectionClass(Mail::class))
            ->getProperty('sentAt');
        $property->setAccessible(true);
        $property->setValue($this->mail, $expected);
        $this::assertSame($expected, $this->mail->getSentAt());
    }

    public function testSetSentAt(): void
    {
        $expected = new DateTime();
        $property = (new ReflectionClass(Mail::class))
            ->getProperty('sentAt');
        $property->setAccessible(true);
        $this->mail->setSentAt($expected);
        $this::assertSame($expected, $property->getValue($this->mail));
    }
}
