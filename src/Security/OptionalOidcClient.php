<?php

namespace App\Security;

use Drenso\OidcBundle\OidcClient;
use Drenso\OidcBundle\OidcJwtHelper;
use Drenso\OidcBundle\OidcSessionStorage;
use Drenso\OidcBundle\OidcUrlFetcher;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Contracts\Cache\CacheInterface;

class OptionalOidcClient extends OidcClient
{
    public function __construct(
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
        string $rememberMeParameter
    ) {
        try {
            parent::__construct(
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
        } catch (\LogicException $exception) {
            // this is fine, continue
        }
    }
}
