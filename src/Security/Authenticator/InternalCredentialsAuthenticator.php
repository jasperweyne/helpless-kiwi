<?php

namespace App\Security\Authenticator;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;

/**
 * This class authenticates users by credentials that are configured within the application itself.
 */
class InternalCredentialsAuthenticator extends AbstractAuthenticator
{
    public const USER = '_credentials_authenticator_user_identfier';
    public const PASS = '_credentials_authenticator_password';

    public function __construct(
        private UserProviderInterface $userProvider,
    ) {
    }

    public static function provideCredentials(Request $request, string $userIdentifier, string $password): void
    {
        $request->attributes->set(self::USER, $userIdentifier);
        $request->attributes->set(self::PASS, $password);
    }

    public function supports(Request $request): ?bool
    {
        return $request->attributes->has(self::USER) && $request->attributes->has(self::PASS);
    }

    public function authenticate(Request $request): Passport
    {
        // Extract credentials from request attributes
        $username = $request->attributes->get(self::USER);
        $password = $request->attributes->get(self::PASS);
        assert(is_string($username) && is_string($password));

        // Cleanup credentials
        $request->attributes->remove(self::USER);
        $request->attributes->remove(self::PASS);

        // Retrieve user
        $user = $this->userProvider->loadUserByIdentifier($username);

        // Validate credentials
        return new Passport(new UserBadge($user->getUserIdentifier()), new PasswordCredentials($password), [new RememberMeBadge()]);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // on success, let the request continue
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        // internal request, assume failure is handled elsewhere
        return null;
    }
}
