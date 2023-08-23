<?php

namespace App\Security;

use Drenso\OidcBundle\Exception\OidcException;
use Drenso\OidcBundle\Model\OidcTokens;
use Drenso\OidcBundle\Model\OidcUserData;
use Drenso\OidcBundle\OidcClientInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * This is just an exception thrower, helpful when debugging security code with oidc turned off.
 */
class MockOidcClient implements OidcClientInterface
{
    public const ERROR_MESSAGE = 'Oidc service is turned off.';

    public function authenticate(Request $request): OidcTokens
    {
        throw new OidcException(self::ERROR_MESSAGE);
    }

    public function refreshTokens(string $refreshToken): OidcTokens
    {
        throw new OidcException(self::ERROR_MESSAGE);
    }

    public function generateAuthorizationRedirect(
        ?string $prompt = null,
        array $scopes = ['openid'],
        bool $forceRememberMe = false,
        array $additionalQueryParams = []
    ): RedirectResponse {
        throw new OidcException(self::ERROR_MESSAGE);
    }

    public function generateEndSessionEndpointRedirect(
        OidcTokens $tokens,
        ?string $postLogoutRedirectUrl,
        array $additionalQueryParams = [],
    ): RedirectResponse {
        throw new OidcException(self::ERROR_MESSAGE);
    }

    public function retrieveUserInfo(OidcTokens $tokens): OidcUserData
    {
        throw new OidcException(self::ERROR_MESSAGE);
    }
}
