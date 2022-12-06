<?php

namespace App\Security;

use App\Repository\ApiTokenRepository;
use Drenso\OidcBundle\Model\OidcTokens;
use Drenso\OidcBundle\OidcClientInterface;
use Drenso\OidcBundle\Security\Token\OidcToken;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Drenso\OidcBundle\Exception\OidcException;
use Drenso\OidcBundle\Security\Exception\OidcAuthenticationException;

/**
 * This class authenticates access token users.
 * So this circumvents any php session cookies, and just uses the javascript way of doing things.
 */
class AccessTokenAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        private OidcClientInterface $oidcClient,
        private LocalUserProvider $provider,
        private ApiTokenRepository $tokenRepository
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
        return $request->headers->has('Authorization');
    }

    public function authenticate(Request $request): Passport
    {
        $matches = [];
        if (1 !== preg_match('/^Bearer ([A-Za-z0-9-_\.\~\+\/]+=*)$/', $request->headers->get('Authorization') ?? '', $matches)) {
            // The token header was empty, authentication fails with HTTP Status
            // Code 401 "Unauthorized"
            throw new CustomUserMessageAuthenticationException('No bearer token provided');
        }
        list(, $token) = $matches;

        // First see if the access token is stored locally
        if (null !== $nativeToken = $this->tokenRepository->find($token)) {
            if (!$nativeToken->isValid()) {
                // cleanup all expired tokens
                $this->tokenRepository->cleanup();

                // note: this message will only be shown once, since on future
                // requests, this token is removed from the database
                throw new CustomUserMessageAuthenticationException('Expired access token provided');
            }

            return new SelfValidatingPassport(new UserBadge($nativeToken->account->getUserIdentifier()));
        }

        if (!isset($_ENV['OIDC_ADDRESS'])) {
            throw new AuthenticationException('Unknown API token');
        }

        // Dump the token in the oidc class, so we can hijack their code and config.
        $tokens = new \stdClass();
        $tokens->access_token = $matches[1];
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

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // on success, let the request continue
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $data = [
            // you may want to customize or obfuscate the message first
            'message' => strtr($exception->getMessageKey(), $exception->getMessageData()),
            // or to translate this message
            // $this->translator->trans($exception->getMessageKey(), $exception->getMessageData())
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }
}
