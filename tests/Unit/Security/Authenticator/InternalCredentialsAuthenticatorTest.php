<?php

namespace Tests\Unit\Security\Authenticator;

use App\Entity\Security\LocalAccount;
use App\Security\Authenticator\InternalCredentialsAuthenticator;
use App\Security\LocalUserProvider;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;

/**
 * Class InternalCredentialsAuthenticatorTest.
 *
 * @covers \App\Security\Authenticator\InternalCredentialsAuthenticator
 */
class InternalCredentialsAuthenticatorTest extends KernelTestCase
{
    protected InternalCredentialsAuthenticator $authenticator;
    protected MockObject&UserProviderInterface $userProvider;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->authenticator = new InternalCredentialsAuthenticator(
            ($this->userProvider = $this->createMock(LocalUserProvider::class)),
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->authenticator);
        unset($this->userProvider);
    }

    public function testSupports(): void
    {
        $request = $this->mockRequest('user');
        $result = $this->authenticator->supports($request);
        self::assertTrue($result);
    }

    public function testAuthenticate(): void
    {
        // Arrange
        $this->userProvider
            ->method('loadUserByIdentifier')
            ->willReturn($user = $this->createMock(LocalAccount::class))
        ;

        $request = $this->mockRequest($username = 'user');
        /** @var MockObject&UserInterface $user */
        $user->method('getUserIdentifier')->willReturn($username);

        // Act
        $result = $this->authenticator->authenticate($request);

        // Assert
        $userBadge = $result->getBadge(UserBadge::class);
        assert($userBadge instanceof UserBadge);
        self::assertSame($userBadge->getUserIdentifier(), $username);
        self::assertFalse($request->attributes->has(InternalCredentialsAuthenticator::USER));
        self::assertFalse($request->attributes->has(InternalCredentialsAuthenticator::PASS));
    }

    public function testOnAuthenticationSuccess(): void
    {
        $request = $this->createMock(Request::class);
        $token = $this->createMock(TokenInterface::class);
        $result = $this->authenticator->onAuthenticationSuccess($request, $token, 'any');
        self::assertNull($result);
    }

    public function testOnAuthenticationFailure(): void
    {
        $request = $this->createMock(Request::class);
        $exception = $this->createMock(AuthenticationException::class);
        $result = $this->authenticator->onAuthenticationFailure($request, $exception);
        self::assertNull($result);
    }

    public function testProvideCredentials(): void
    {
        $request = $this->createMock(Request::class);
        $request->attributes = new ParameterBag();
        InternalCredentialsAuthenticator::provideCredentials($request, $username = 'username', $password = 'password');
        self::assertTrue($request->attributes->has(InternalCredentialsAuthenticator::USER));
        self::assertTrue($request->attributes->has(InternalCredentialsAuthenticator::PASS));
        self::assertSame($username, $request->attributes->get(InternalCredentialsAuthenticator::USER));
        self::assertSame($password, $request->attributes->get(InternalCredentialsAuthenticator::PASS));
    }

    private function mockRequest(string $username, string $password = 'password'): MockObject&Request
    {
        /** @var MockObject&Request $request */
        $request = $this->createMock(Request::class);
        $request->attributes = new ParameterBag([
            InternalCredentialsAuthenticator::USER => $username,
            InternalCredentialsAuthenticator::PASS => $password,
        ]);
        return $request;
    }
}
