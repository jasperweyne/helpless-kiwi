<?php

namespace Tests\Unit\Security\Authenticator;

use App\Security\Authenticator\AbstractBearerTokenAuthenticator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;

/**
 * Class AbstractBearerTokenAuthenticatorTest.
 *
 * @covers \App\Security\Authenticator\AbstractBearerTokenAuthenticator
 */
class AbstractBearerTokenAuthenticatorTest extends KernelTestCase
{
    private MockObject&AbstractBearerTokenAuthenticator $authenticator;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Client mock
        $this->authenticator = $this->getMockForAbstractClass(AbstractBearerTokenAuthenticator::class);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->authenticator);
    }

    public function testSupports(): void
    {
        // Arrange
        $request = $this->createMock(Request::class);
        $headers = $this->createMock(HeaderBag::class);
        $headers->method('has')->willReturnMap([
            ['Authorization', true]
        ]);
        $request->headers = $headers;

        // Act
        $result = $this->authenticator->supports($request);

        // Assert
        self::assertTrue($result);
    }

    public function testAuthenticate(): void
    {
        // Arrange
        $token = '1234';
        $request = self::mockRequestWithAuthHeader($this, "Bearer $token");
        $passport = $this->createMock(Passport::class);

        $this->authenticator
            ->expects(self::once())
            ->method('authenticateBearerToken')
            ->with($request, $token)
            ->willReturn($passport)
        ;

        // Act
        $result = $this->authenticator->authenticate($request);

        // Assert
        self::assertSame($passport, $result);
    }

    public function testAuthenticateWithInvalidToken(): void
    {
        $request = self::mockRequestWithAuthHeader($this, "Bearer 123 abcdefg");

        self::expectException(AuthenticationException::class);

        $this->authenticator
            ->expects(self::never())
            ->method('authenticateBearerToken')
        ;

        $this->authenticator->authenticate($request);
    }

    public static function mockRequestWithAuthHeader(TestCase $by, string $header): Request
    {
        $request = $by->createMock(Request::class);
        $headers = $by->createMock(HeaderBag::class);
        $valueMap = [
            ['Authorization', null, $header]
        ];
        $headers->method('get')->willReturnMap($valueMap);
        $request->headers = $headers;
        return $request;
    }

    public function testOnAuthenticationSuccess(): void
    {
        // Arrange
        $token = $this->createMock(TokenInterface::class);
        $request = $this->createMock(Request::class);
        $request->attributes = new ParameterBag(['_security_firewall_run' => '_security_session']);

        // Act
        $this->authenticator->onAuthenticationSuccess($request, $token, 'any');

        // Assert
        $stateless = $request->attributes->get('_security_firewall_run');
        self::assertIsString($stateless);
        self::assertStringStartsNotWith('_security_', $stateless);
    }

    public function testOnAuthenticationFailure(): void
    {
        /* @todo This test is incomplete. */
        self::markTestIncomplete();
    }
}
