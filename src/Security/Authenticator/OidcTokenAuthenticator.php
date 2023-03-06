<?php

namespace App\Security\Authenticator;

use App\Security\LocalUserProvider;
use Drenso\OidcBundle\Model\OidcTokens;
use Drenso\OidcBundle\OidcClientInterface;
use Drenso\OidcBundle\Security\Token\OidcToken;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Drenso\OidcBundle\Exception\OidcException;
use Drenso\OidcBundle\Security\Exception\OidcAuthenticationException;

/**
 * This class authenticates OAuth2 access tokens for the currently configured OIDC client.
 */
class OidcTokenAuthenticator extends AbstractBearerTokenAuthenticator
{
    public function __construct(
        private OidcClientInterface $oidcClient,
        private LocalUserProvider $provider
    ) {
    }

    /**
     * Called on every request to decide if this authenticator should be
     * used for the request. Returning `false` will cause this authenticator
     * to be skipped.
     *
     * This is the first authenticator to be checked, and it is skipped if the Bearer token is not provided.
     */
    public function supports(Request $request): ?bool
    {
        return parent::supports($request) === true
            && str_starts_with($request->getRequestUri(), '/api/')
            && isset($_ENV['OIDC_ADDRESS']);
    }

    protected function authenticateBearerToken(Request $request, string $bearerToken): Passport
    {
        // Dump the token in the oidc class, so we can hijack their code and config.
        $tokens = new \stdClass();
        $tokens->access_token = $bearerToken;
        $tokens->id_token = 'not used';
        $authData = new OidcTokens($tokens);

        try {
            // Retrieve the user data with the authentication data
            $userData = $this->oidcClient->retrieveUserInfo($authData);

            // Ensure the user exists
            if ('' === $userIdentifier = $userData->getSub()) {
                throw new UserNotFoundException();
            }

            $this->provider->ensureUserExists($userIdentifier, $userData);
        } catch (OidcException $e) {
            throw new OidcAuthenticationException('OIDC authentication failed', $e);
        }

        // Create the passport
        $passport = new SelfValidatingPassport(new UserBadge(
            $userIdentifier,
            fn (string $userIdentifier) => $this->provider->loadOidcUser($userIdentifier),
        ));
        $passport->setAttribute(OidcToken::AUTH_DATA_ATTR, $authData);
        $passport->setAttribute(OidcToken::USER_DATA_ATTR, $userData);

        return $passport;
    }
}
