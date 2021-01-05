<?php

namespace Tests\Unit\EventSubscriber;

use App\EventSubscriber\ProfileUpdateSubscriber;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class ProfileUpdateSubscriberTest.
 *
 * @covers \App\EventSubscriber\ProfileUpdateSubscriber
 */
class ProfileUpdateSubscriberTest extends KernelTestCase
{
    /**
     * @var ProfileUpdateSubscriber
     */
    protected $profileUpdateSubscriber;

    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var UrlGeneratorInterface
     */
    protected $urlGenerator;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        $this->tokenStorage = self::$container->get(TokenStorageInterface::class);
        $this->urlGenerator = self::$container->get(UrlGeneratorInterface::class);
        $this->profileUpdateSubscriber = new ProfileUpdateSubscriber($this->tokenStorage, $this->urlGenerator);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->profileUpdateSubscriber);
        unset($this->tokenStorage);
        unset($this->urlGenerator);
    }

    public function testOnRequest(): void
    {
        /* @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testCheckProfileUpdate(): void
    {
        /* @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testGetSubscribedEvents(): void
    {
        /* @todo This test is incomplete. */
        $this->markTestIncomplete();
    }
}
