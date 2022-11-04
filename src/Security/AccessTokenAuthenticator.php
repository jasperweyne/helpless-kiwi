<?php

namespace App\Security;

use Drenso\OidcBundle\Model\OidcTokens;
use Drenso\OidcBundle\OidcClient;
use Drenso\OidcBundle\Security\Token\OidcToken;
use stdClass;
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
use Symfony\Component\HttpKernel\Log\Logger;

/**
 * This class authenticates access token users.
 * So this circumvents any php session cookies, and just uses the javascript way of doing things.
 */
class AccessTokenAuthenticator extends AbstractAuthenticator
{
        
    /**
     * @var OidcClient
     */
    private $oidcClient;

    public function __construct(OidcClient $oidcClient)
    {
        $this->oidcClient = $oidcClient;
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
        $apiToken = $request->headers->get('Authorization');
        $matches = [];

        if (null === $apiToken || 1 !== preg_match('/^Bearer ([A-Za-z0-9-_\.\~\+\/]+=*)$/', $apiToken, $matches)) {
            // The token header was empty, authentication fails with HTTP Status
            // Code 401 "Unauthorized"
            throw new CustomUserMessageAuthenticationException('No API token provided');
        }

        // Dump the token in the oidc class, so we can hijack their code and config.
        $tokens = new stdClass();
        $tokens->access_token = $apiToken;
        $tokens->id_token = 'not used';
        $authData = new OidcTokens($tokens);

        // Steals your code, while your not looking.
        try {
            // Retrieve the user data with the authentication data
            $userData = $this->oidcClient->retrieveUserInfo($authData);

            // Ensure the user exists
            if (!$userIdentifier = $userData->getUserDataString('sub')) {
                throw new UserNotFoundException(
                    sprintf(
                        'User identifier property (%s) yielded empty user identifier',
                        $this->userIdentifierProperty
                        )
                    );
            }
            $this->oidcUserProvider->ensureUserExists($userIdentifier, $userData);
        
            // Create the passport
            $passport = new SelfValidatingPassport(new UserBadge(
                $userIdentifier,
                fn (string $userIdentifier) => $this->oidcUserProvider->loadOidcUser($userIdentifier),
            ));
            $passport->setAttribute(OidcToken::AUTH_DATA_ATTR, $authData);
            $passport->setAttribute(OidcToken::USER_DATA_ATTR, $userData);
        
            return $passport;
        } catch (OidcException $e) {
            throw new OidcAuthenticationException('OIDC authentication failed', $e);
        }
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // on success, let the request continue
        return 'ee';
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
