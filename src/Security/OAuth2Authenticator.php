<?php

namespace App\Security;

use App\EventSubscriber\ProfileUpdateSubscriber;
use App\Security\OAuth2UserProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use OpenIDConnectClient\Exception\InvalidTokenException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use OpenIDConnectClient\OpenIDConnectProvider;

class OAuth2Authenticator extends AbstractGuardAuthenticator
{
    use TargetPathTrait;

    private $provider;

    public function __construct(OpenIDConnectProvider $provider)
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
        } catch (InvalidTokenException $e) {
            $msgs = json_encode($this->provider->getValidatorChain()->getMessages());
            throw new InvalidTokenException($e->getMessage() . '  ' . $msgs, 0, $e);
        }

        return $credentials;
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        /// Check Credentials
        if (!$this->_checkCredentials($credentials))
            throw new AuthenticationException();

        // Load / create our user however you need.
        $user = $userProvider->loadUserByUsername($credentials['accessToken']);

        if (!$user) {
            // fail authentication with a custom error
            throw new CustomUserMessageAuthenticationException('Username could not be found.');
        }

        return $user;
    }

    private function _checkCredentials($credentials)
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

    public function checkCredentials($credentials, UserInterface $user)
    {
        // This message is executed _after_ getUser() is executed.
        // This is obviously a security problem for OAuth2, therefore 
        // we don't use this method.
        // Source:  https://symfony.com/doc/current/security/guard_authentication.html#guard-auth-methods  
        return true;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        // todo: set flash error
        // todo: redirect user to the last page accessed
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        $targetPath = $this->getTargetPath($request->getSession(), $providerKey);

        // Redirect to target
        if ($targetPath) {
            return new RedirectResponse($targetPath);
        }

        // If no target, redirect to home
        return new RedirectResponse('/');
    }
}
