<?php

namespace Tests\Unit\EventSubscriber;

use App\Calendar\ICalProvider;
use App\Entity\Activity\Activity;
use App\Entity\Activity\Registration;
use App\Event\RegistrationAddedEvent;
use App\Event\RegistrationRemovedEvent;
use App\EventSubscriber\MailNotificationSubscriber;
use App\Mail\MailService;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Security\Core\Security;
use Twig\Environment;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class MailNotificationSubscriberTest.
 *
 * @covers \App\EventSubscriber\MailNotificationSubscriber
 */
final class MailNotificationSubscriberTest extends KernelTestCase
{
    /**
     * @var MailNotificationSubscriber
     */
    private $mailNotificationSubscriber;

    /**
     * @var Environment&MockObject
     */
    private $template;

    /**
     * @var MailService&MockObject
     */
    private $mailer;

    /**
     * @var ICalProvider&MockObject
     */
    private $calendar;

    /**
     * @var Security&MockObject
     */
    private $security;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->template = $this->createMock(Environment::class);
        $this->mailer = $this->createMock(MailService::class);
        $this->calendar = $this->createMock(ICalProvider::class);
        $this->security = $this->createMock(Security::class);
        $this->mailNotificationSubscriber = new MailNotificationSubscriber($this->template, $this->mailer, $this->calendar, $this->security);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->mailNotificationSubscriber);
        unset($this->template);
        unset($this->mailer);
        unset($this->calendar);
        unset($this->security);
    }

    public function testGetSubscribedEvents(): void
    {
        // extract all method names
        $handlers = [];
        foreach (MailNotificationSubscriber::getSubscribedEvents() as $value) {
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

        // check if all methods exist
        foreach ($handlers as $handler) {
            self::assertTrue(method_exists($this->mailNotificationSubscriber, $handler), "Method $handler does not exist");
        }
    }

    public function testNotifyRegistrationAdded(): void
    {
        /** @var Registration&MockObject */
        $registration = $this->createMock(Registration::class);
        $registration->method('getActivity')->willReturn($this->createMock(Activity::class));

        $this->template->method('render')->willReturn('<html>');
        $this->mailer->expects(self::once())->method('message');

        $event = new RegistrationAddedEvent($registration);
        $this->mailNotificationSubscriber->notifyRegistrationAdded($event);
    }

    public function testNotifyRegistrationAddedReserve(): void
    {
        /** @var Registration&MockObject */
        $registration = $this->createMock(Registration::class);
        $registration->method('isReserve')->willReturn(true);

        $this->mailer->expects(self::never())->method('message');

        $event = new RegistrationAddedEvent($registration);
        $this->mailNotificationSubscriber->notifyRegistrationAdded($event);
    }

    public function testNotifyRegistrationRemoved(): void
    {
        /** @var Registration&MockObject */
        $registration = $this->createMock(Registration::class);
        $registration->method('getActivity')->willReturn($this->createMock(Activity::class));

        $this->template->method('render')->willReturn('<html>');
        $this->mailer->expects(self::once())->method('message');

        $event = new RegistrationRemovedEvent($registration);
        $this->mailNotificationSubscriber->notifyRegistrationRemoved($event);
    }

    public function testNotifyRegistrationRemovedReserve(): void
    {
        /** @var Registration&MockObject */
        $registration = $this->createMock(Registration::class);
        $registration->method('isReserve')->willReturn(true);

        $this->mailer->expects(self::never())->method('message');

        $event = new RegistrationRemovedEvent($registration);
        $this->mailNotificationSubscriber->notifyRegistrationRemoved($event);
    }
}
