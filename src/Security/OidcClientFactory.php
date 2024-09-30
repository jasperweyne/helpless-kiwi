<?php

namespace App\Security;

use Drenso\OidcBundle\OidcClient;
use Drenso\OidcBundle\OidcClientInterface;
use Drenso\OidcBundle\OidcJwtHelper;
use Drenso\OidcBundle\OidcSessionStorage;
use Drenso\OidcBundle\OidcUrlFetcher;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Contracts\Cache\CacheInterface;

/**
 * Factory which build the service.
 */
class OidcClientFactory
{
    public static function createOidcClient(
        RequestStack $requestStack,
        HttpUtils $httpUtils,
        ?CacheInterface $wellKnownCache,
        OidcUrlFetcher $urlFetcher,
        OidcSessionStorage $sessionStorage,
        OidcJwtHelper $jwtHelper,
        string $wellKnownUrl,
        ?int $wellKnownCacheTime,
        string $clientId,
        string $clientSecret,
        string $redirectRoute,
        string $rememberMeParameter,
    ): OidcClientInterface {
        // True oidc client, for when oidc is enabled.
        // This is the regular oidc client, which should function as advertised.
        if (isset($_ENV['OIDC_ADDRESS']) && '' !== $_ENV['OIDC_ADDRESS']) {
            return new OidcClient(
                $requestStack,
                $httpUtils,
                $wellKnownCache,
                $urlFetcher,
                $sessionStorage,
                $jwtHelper,
                $wellKnownUrl,
                $wellKnownCacheTime,
                $clientId,
                $clientSecret,
                $redirectRoute,
                $rememberMeParameter
            );
        }

        // Mock oidc client, for when oidc is disabled.
        // Oidc is turned off, so this class should never be called in correct operation.
        // Therefore this client gives a oidcclient exception when it is incorrectly called.
        return new MockOidcClient();
    }
}
