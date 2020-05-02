<?php

namespace App\Security;

use App\EventSubscriber\ProfileUpdateSubscriber;
use App\Security\OAuth2UserProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use League\OAuth2\Client\Provider\GenericProvider;

class OAuth2Authenticator extends AbstractGuardAuthenticator
{
    use TargetPathTrait;

    private $provider;

    public function __construct(GenericProvider $provider)
    {
        $this->provider = $provider;
    }

    public function supportsRememberMe()
    {
        // todo: check how to make this work
        return false;
    }

    public function supports(Request $request)
    {
        return 'app_login' === $request->attributes->get('_route')
            && $request->isMethod('GET')
            && $request->query->has('state')
            && $request->query->has('code')
        ;
    }

    public function start(Request $request, ?AuthenticationException $authException = null)
    {
        // Fetch the authorization URL from the provider; this returns the
        // urlAuthorize option and generates and applies any necessary parameters
        // (e.g. state).
        $authorizationUrl = $this->provider->getAuthorizationUrl();
    
        // Get the state generated for you and store it to the session.
        $request->getSession()->set('oauth2state', $this->provider->getState());
    
        // Redirect the user to the authorization URL.
        return new RedirectResponse($authorizationUrl);
    }

    public function getCredentials(Request $request)
    {
        // Get state
        $credentials = [
            'validationState' => $request->query->get('state'),
            'submittedState' => $request->getSession()->remove('oauth2state'),
        ];

        // Try to exchange authorization code for access token
        try {
            $credentials['accessToken'] = $this->provider->getAccessToken('authorization_code', [
                'code' => $request->query->get('code'),
            ]);
        } catch (IdentityProviderException $e) {
            $credentials['exception'] = $e;
        }

        return $credentials;
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        if (!$userProvider instanceof OAuth2UserProvider) {
            throw new \LogicException('User provider not supported!');
        }

        // Load / create our user however you need.
        // You can do this by calling the user provider, or with custom logic here.
        $user = $userProvider->loadUserByUsername($credentials['accessToken']);

        if (!$user) {
            // fail authentication with a custom error
            throw new CustomUserMessageAuthenticationException('Username could not be found.');
        }

        return $user;
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        if (is_null($credentials['submittedState']) || $credentials['submittedState'] !== $credentials['validationState']) {
            throw new AuthenticationException('Invalid state returned by authentication server');

        }

        if (array_key_exists('exception', $credentials)) {
            throw $credentials['exception'];
        }

        if ($credentials['accessToken']->hasExpired()) {
            throw new AuthenticationException('Access token has expired');
        }
        
        return true;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        // todo: set flash error
        // todo: redirect user to the last page accessed
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        $auth = $token->getUser();
        $auth->setLastLogin(new \DateTime());

        $this->em->persist($auth);
        $this->em->flush();

        $targetPath = $this->getTargetPath($request->getSession(), $providerKey);

        // First, check if admin page requested
        // If so, skip the profile update check
        $adminPrefix = $request->getSchemeAndHttpHost().'/admin';
        if (substr($targetPath ?? '', 0, strlen($adminPrefix)) === $adminPrefix) {
            return new RedirectResponse($targetPath);
        }

        // Execute the profile update check, and redirect if necessary
        if (ProfileUpdateSubscriber::checkProfileUpdate($auth)) {
            return new RedirectResponse($this->urlGenerator->generate('profile_update'));
        }

        // If no profile update required, redirect to target
        if ($targetPath) {
            return new RedirectResponse($targetPath);
        }

        // If no target, redirect to home
        return new RedirectResponse('/');
    }
}
