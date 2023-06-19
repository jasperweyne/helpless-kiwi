<?php

namespace Tests\Unit\EventSubscriber;

use App\Calendar\ICalProvider;
use App\Entity\Activity\Activity;
use App\Entity\Activity\Registration;
use App\Entity\Security\LocalAccount;
use App\Event\RegistrationAddedEvent;
use App\Event\RegistrationRemovedEvent;
use App\Event\Security\CreateAccountsEvent;
use App\EventSubscriber\MailNotificationSubscriber;
use App\Mail\MailService;
use App\Security\PasswordResetService;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Security\Core\Security;
use Twig\Environment;

/**
 * Class MailNotificationSubscriberTest.
 *
 * @covers \App\EventSubscriber\MailNotificationSubscriber
 */
final class MailNotificationSubscriberTest extends KernelTestCase
{
    private MailNotificationSubscriber $mailNotificationSubscriber;
    private Environment&MockObject $template;
    private MailService&MockObject $mailer;
    private PasswordResetService&MockObject $passwordReset;
    private LocalAccount&MockObject $localAccount;
    private Security&MockObject $security;
    private Registration&MockObject $registration;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->template = self::createMock(Environment::class);
        $this->mailer = self::createMock(MailService::class);
        $this->security = self::createMock(Security::class);
        $this->passwordReset = self::createMock(PasswordResetService::class);
        $this->localAccount = self::createMock(LocalAccount::class);
        $this->registration = self::createMock(Registration::class);

        $this->mailNotificationSubscriber = new MailNotificationSubscriber(
            $this->template,
            $this->mailer,
            self::createMock(ICalProvider::class),
            $this->passwordReset,
            $this->security
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->template);
        unset($this->mailer);
        unset($this->security);
        unset($this->passwordReset);
        unset($this->localAccount);
        unset($this->registration);

        unset($this->mailNotificationSubscriber);
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
            self::assertTrue(
                method_exists($this->mailNotificationSubscriber, $handler),
                "Method $handler does not exist"
            );
        }
    }

    public function testNotifyRegistrationAdded(): void
    {
        $this->registration
            ->method('getActivity')
            ->willReturn(self::createMock(Activity::class));

        $this->template->method('render')->willReturn('<html>');
        $this->mailer->expects(self::once())->method('message');

        $event = new RegistrationAddedEvent($this->registration);
        $this->mailNotificationSubscriber->notifyRegistrationAdded($event);
    }

    public function testNotifyRegistrationAddedReserve(): void
    {
        $this->registration->method('isReserve')->willReturn(true);

        $this->mailer->expects(self::never())->method('message');

        $event = new RegistrationAddedEvent($this->registration);
        $this->mailNotificationSubscriber->notifyRegistrationAdded($event);
    }

    public function testNotifyRegistrationRemoved(): void
    {
        $this->registration
            ->method('getActivity')
            ->willReturn(self::createMock(Activity::class));

        $this->template->method('render')->willReturn('<html>');
        $this->mailer->expects(self::once())->method('message');

        $event = new RegistrationRemovedEvent($this->registration);
        $this->mailNotificationSubscriber->notifyRegistrationRemoved($event);
    }

    public function testNotifyRegistrationRemovedReserve(): void
    {
        $this->registration->method('isReserve')->willReturn(true);

        $this->mailer->expects(self::never())->method('message');

        $event = new RegistrationRemovedEvent($this->registration);
        $this->mailNotificationSubscriber->notifyRegistrationRemoved($event);
    }

    public function testNotifyCreateAccount(): void
    {
        $event = new CreateAccountsEvent([$this->localAccount]);

        $this->localAccount
            ->expects($this::once())
            ->method('getOidc')
            ->willReturn(null);
        $this->localAccount
            ->expects($this::once())
            ->method('getPassword')
            ->willReturn(null);
        $this->passwordReset
            ->expects($this::once())
            ->method('generatePasswordRequestToken')
            ->with($this->localAccount, false)
            ->willReturn('test_token');
        $this->localAccount
            ->expects($this::once())
            ->method('setPasswordRequestedAt')
            ->with(null);
        $this->template
            ->expects($this::once())
            ->method('render')
            ->willReturn('test_email_content');
        $this->mailer
            ->expects($this::once())
            ->method('message')
            ->with(
                [$this->localAccount],
                'Jouw account',
                'test_email_content'
            );

        $this->mailNotificationSubscriber->notifyCreateAccount($event);
    }

    public function testNotifyCreateAccountWithLoginCredentials(): void
    {
        $this->localAccount
            ->method('getOidc')
            ->willReturn('mock_oidc');
        $this->localAccount
            ->method('getPassword')
            ->willReturn('mock_password');
        $event = new CreateAccountsEvent([$this->localAccount]);

        $this->mailer
            ->expects(self::never())
            ->method('message');

        $this->mailNotificationSubscriber->notifyCreateAccount($event);
    }
}
