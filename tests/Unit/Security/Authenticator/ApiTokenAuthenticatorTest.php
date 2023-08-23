<?php

namespace Tests\Unit\Security\Authenticator;

use App\Entity\Security\ApiToken;
use App\Entity\Security\LocalAccount;
use App\Entity\Security\TrustedClient;
use App\Repository\ApiTokenRepository;
use App\Security\Authenticator\ApiTokenAuthenticator;
use App\Security\LocalUserProvider;
use Drenso\OidcBundle\OidcClientInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\CredentialsExpiredException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;

/**
 * Class ApiTokenAuthenticatorTest.
 *
 * @covers \App\Security\Authenticator\ApiTokenAuthenticator
 */
class ApiTokenAuthenticatorTest extends KernelTestCase
{
    protected ApiTokenAuthenticator $auth;

    protected OidcClientInterface $mockOidcClient;
    protected LocalUserProvider $mockUserProvider;
    protected ApiTokenRepository $mockTokenRepo;

    protected ApiToken $validToken;
    protected ApiToken $unvalidToken;

    protected LocalAccount $user;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Client mock
        $this->mockOidcClient = $this->createMock(OidcClientInterface::class);

        // User mock
        $this->mockUserProvider = $this->createMock(LocalUserProvider::class);

        $this->user = $this->createMock(LocalAccount::class);
        $this->user->method('getUserIdentifier')->willReturn('userid');

        // Native Tokens
        $trustClient = $this->createMock(TrustedClient::class);
        $expdate = new \DateTimeImmutable('-30 minutes');
        $nowdate = new \DateTimeImmutable('+5 minutes');

        $this->validToken = new ApiToken($this->user, $trustClient, $nowdate);
        $this->unvalidToken = new ApiToken($this->user, $trustClient, $expdate);

        // Token repo
        $this->mockTokenRepo = $this->createMock(ApiTokenRepository::class);
        $tokenMap = [
            [$this->validToken->token, null, null, $this->validToken],
            [$this->unvalidToken->token, null, null, $this->unvalidToken],
        ];
        $this->mockTokenRepo->method('find')->willReturnMap($tokenMap);

        // Authenticator
        $this->auth = new ApiTokenAuthenticator(
            $this->mockTokenRepo
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->auth);
        unset($this->mockOidcClient);
        unset($this->mockUserProvider);
        unset($this->mockTokenRepo);
    }

    public function testSupports(): void
    {
        $request = $this->mockRequest('1234');
        self::assertTrue($this->auth->supports($request));
    }

    public function testAuthenticate(): void
    {
        // Valid native token
        $request = $this->mockRequest($this->validToken->token);
        $passport = $this->auth->authenticate($request);
        $badge = $passport->getBadge(UserBadge::class);
        self::assertNotNull($badge);
        self::assertInstanceOf(UserBadge::class, $badge);
        self::assertSame($badge->getUserIdentifier(), $this->user->getUserIdentifier());

        // Unvalid native token
        $request = $this->mockRequest($this->unvalidToken->token);
        self::expectException(CredentialsExpiredException::class);
        $this->auth->authenticate($request);
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
