<?php

namespace Tests\Unit\EventSubscriber;

use App\Entity\Activity\Registration;
use App\Entity\Security\LocalAccount;
use App\Event\RegistrationAddedEvent;
use App\Event\RegistrationRemovedEvent;
use App\EventSubscriber\RegistrationSubscriber;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;
use Symfony\Component\Security\Core\Security;

/**
 * Class RegistrationSubscriberTest.
 *
 * @covers \App\EventSubscriber\RegistrationSubscriber
 */
final class RegistrationSubscriberTest extends KernelTestCase
{
    /**
     * @var RegistrationSubscriber
     */
    private $registrationSubscriber;

    /**
     * @var EntityManagerInterface&MockObject
     */
    private $em;

    /**
     * @var FlashBagInterface&MockObject
     */
    private $flash;

    /**
     * @var LocalAccount&MockObject
     */
    private $user;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->user = $this->createMock(LocalAccount::class);
        $this->user->method('getId')->willReturn('a');

        $stack = $this->createMock(RequestStack::class);
        $stack->method('getSession')->willReturn($session = $this->createMock(FlashBagAwareSessionInterface::class));
        $session->method('getFlashBag')->willReturn($this->flash = $this->createMock(FlashBagInterface::class));

        $security = $this->createMock(Security::class);
        $security->method('getUser')->willReturn($this->user);
        $this->registrationSubscriber = new RegistrationSubscriber($this->em, $stack, $security);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->registrationSubscriber);
        unset($this->em);
        unset($this->flash);
        unset($this->user);
    }

    public function testGetSubscribedEvents(): void
    {
        // extract all method names
        $handlers = [];
        foreach (RegistrationSubscriber::getSubscribedEvents() as $value) {
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
            self::assertTrue(method_exists($this->registrationSubscriber, $handler), "Method $handler does not exist");
        }
    }

    public function testPersistRegistrationAdded(): void
    {
        /** @var Registration&MockObject */
        $registration = $this->createMock(Registration::class);
        $registration->method('getPerson')->willReturn($this->user);

        // check database
        $this->em->expects(self::once())
            ->method('persist')
            ->with($registration);
        $this->em->expects(self::once())
            ->method('flush');

        // check flash
        $this->flash->expects(self::once())
            ->method('add')
            ->with('success');

        $event = new RegistrationAddedEvent($registration);
        $this->registrationSubscriber->persistRegistrationAdded($event);
    }

    public function testPersistRegistrationRemoved(): void
    {
        /** @var Registration&MockObject */
        $registration = $this->createMock(Registration::class);
        $registration->method('getPerson')->willReturn($this->user);
        $registration->expects(self::once())->method('setDeleteDate')->with(self::isInstanceOf(\DateTime::class));

        // check database
        $this->em->expects(self::once())
            ->method('flush');

        // check flash
        $this->flash->expects(self::once())
            ->method('add')
            ->with('success');

        $event = new RegistrationRemovedEvent($registration);
        $this->registrationSubscriber->persistRegistrationRemoved($event);
    }
}
