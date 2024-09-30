<?php

namespace App\Security\Authenticator;

use App\Repository\ApiTokenRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\CredentialsExpiredException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

/**
 * This class authenticates Kiwi API tokens.
 */
class ApiTokenAuthenticator extends AbstractBearerTokenAuthenticator
{
    public function __construct(
        private ApiTokenRepository $tokenRepository,
    ) {
    }

    public function supports(Request $request): ?bool
    {
        return true === parent::supports($request) && str_starts_with($request->getRequestUri(), '/api/');
    }

    protected function authenticateBearerToken(Request $request, string $bearerToken): Passport
    {
        // First see if the access token is stored locally
        if (null === $token = $this->tokenRepository->find($bearerToken)) {
            throw new BadCredentialsException();
        }

        if (!$token->isValid()) {
            // cleanup all expired tokens
            $this->tokenRepository->cleanup();

            // note: this message will only be shown once, since on future
            // requests, this token is removed from the database
            throw new CredentialsExpiredException();
        }

        return new SelfValidatingPassport(new UserBadge($token->account->getUserIdentifier()));
    }
}
