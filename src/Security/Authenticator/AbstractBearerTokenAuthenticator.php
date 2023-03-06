<?php

namespace App\Security\Authenticator;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;

/**
 * This class authenticates access token users.
 * So this circumvents any php session cookies, and just uses the javascript way of doing things.
 */
abstract class AbstractBearerTokenAuthenticator extends AbstractAuthenticator
{
    /**
     * Called on every request to decide if this authenticator should be
     * used for the request. Returning `false` will cause this authenticator
     * to be skipped.
     */
    public function supports(Request $request): ?bool
    {
        return $request->headers->has('Authorization') && null !== $this->extractBearerToken($request);
    }

    abstract protected function authenticateBearerToken(Request $request, string $bearerToken): Passport;

    public function authenticate(Request $request): Passport
    {
        $token = $this->extractBearerToken($request);
        assert(is_string($token));

        return $this->authenticateBearerToken($request, $token);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // This disables the ContextListener from writing the login to the session, making this login stateless
        // See Symfony\Component\Security\Http\Firewall\ContextListener::onKernelResponse
        $request->attributes->set('_security_firewall_run', '');

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

    private function extractBearerToken(Request $request): ?string
    {
        $authorization = $request->headers->get('Authorization') ?? '';
        $matches = [];
        $result = preg_match('/^Bearer ([A-Za-z0-9-_\.\~\+\/]+=*)$/', $authorization, $matches);

        return 1 === $result ? $matches[1] : null;
    }
}
