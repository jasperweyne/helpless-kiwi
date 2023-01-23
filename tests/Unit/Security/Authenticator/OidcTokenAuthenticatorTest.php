<?php

namespace Tests\Unit\Security\Authenticator;

use App\Security\Authenticator\OidcTokenAuthenticator;
use App\Security\LocalUserProvider;
use Drenso\OidcBundle\Model\OidcUserData;
use Drenso\OidcBundle\OidcClientInterface;
use Drenso\OidcBundle\Security\Token\OidcToken;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

/**
 * Class OidcTokenAuthenticatorTest.
 *
 * @covers \App\Security\Authenticator\OidcTokenAuthenticator
 */
class OidcTokenAuthenticatorTest extends KernelTestCase
{
    protected OidcTokenAuthenticator $authenticator;
    protected MockObject&OidcClientInterface $oidcClient;
    protected MockObject&UserProviderInterface $userProvider;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $_ENV['OIDC_ADDRESS'] = 'test';
        $this->authenticator = new OidcTokenAuthenticator(
            ($this->oidcClient = $this->createMock(OidcClientInterface::class)),
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
        unset($this->oidcClient);
        unset($this->userProvider);
    }

    public function testSupports(): void
    {
        $request = $this->mockRequest('1234');
        $result = $this->authenticator->supports($request);
        self::assertTrue($result);
    }

    public function testAuthenticate(): void
    {
        // Arrange
        $this->oidcClient
            ->expects(self::once())
            ->method('retrieveUserInfo')
            ->willReturn($userData = $this->createMock(OidcUserData::class))
        ;

        $userData
            ->method('getSub')
            ->willReturn($userIdentifier = 'test123')
        ;

        $this->userProvider
            ->expects(self::once())
            ->method('ensureUserExists')
            ->with($userIdentifier, $userData)
        ;

        $request = $this->mockRequest('token');

        // Act
        $result = $this->authenticator->authenticate($request);

        // Assert
        self::assertInstanceOf(SelfValidatingPassport::class, $result);
        $userBadge = $result->getBadge(UserBadge::class);
        assert($userBadge instanceof UserBadge);
        self::assertSame($userBadge->getUserIdentifier(), $userIdentifier);
        self::assertNotNull($result->getAttribute(OidcToken::AUTH_DATA_ATTR));
        self::assertNotNull($result->getAttribute(OidcToken::USER_DATA_ATTR));
    }

    public function testAuthenticateUnknown(): void
    {
        $this->oidcClient
            ->expects(self::once())
            ->method('retrieveUserInfo')
            ->willReturn($userData = $this->createMock(OidcUserData::class))
        ;

        $userData
            ->method('getSub')
            ->willReturn('')
        ;

        $this->userProvider
            ->expects(self::never())
            ->method('ensureUserExists')
        ;

        self::expectException(UserNotFoundException::class);
        $this->authenticator->authenticate($this->mockRequest('token'));
    }

    private function mockRequest(string $token): MockObject&Request
    {
        /** @var MockObject&Request $request */
        $request = $this->createMock(Request::class);
        $request->method('getRequestUri')->willReturn('/api/');
        $request->headers = new HeaderBag([
            'Authorization' => "Bearer $token",
        ]);
        return $request;
    }
}
