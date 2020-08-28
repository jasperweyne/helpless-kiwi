<?php

namespace App\Security;

use App\Entity\Security\OAuth2AccessToken;
use App\Provider\Person\Person;
use App\Security\OAuth2User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use OpenIDConnectClient\OpenIDConnectProvider;
use OpenIDConnectClient\AccessToken;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Intl\Exception\MethodNotImplementedException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationExpiredException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Http\Logout\LogoutHandlerInterface;
use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;

class OAuth2UserProvider implements UserProviderInterface, LogoutHandlerInterface, LogoutSuccessHandlerInterface
{
    private $provider;
    private $em;

    public function __construct(OpenIDConnectProvider $provider, EntityManagerInterface $em)
    {
        $this->provider = $provider;
        $this->em = $em;
    }

    public function loadUserByUsername($token)
    {
        if (!$token instanceof AccessToken)
            throw new UsernameNotFoundException();

        $tokenArray = $token->getValues();
        $roles = ['ROLE_OAUTH2'];
        if (isset($tokenArray['scope']) && false !== strpos($tokenArray['scope'], 'admin'))
            $roles[] = 'ROLE_ADMIN';

        $idToken = $token->getIdToken();

        $person = new Person();
        $person
            ->setId($idToken->getClaim('sub'))
            ->setEmail($idToken->getClaim('email'))
            ->setFields(($idToken->getClaims()))
        ;
        
        $user = new OAuth2User();
        $user
            ->setId($token->getIdToken()->getClaim('sub'))
            ->setRoles($roles)
            ->setPerson($person)
        ;

        $dbToken = $this->em->getRepository(OAuth2AccessToken::class)->findOneBy(['id' => $user->getId()]);

        if (is_null($dbToken)) {
            $dbToken = new OAuth2AccessToken();
            $dbToken->setId($user->getId());

            $this->em->persist($dbToken);
        }

        // We store the access token seperated from the User for security
        $dbToken->setAccessToken($token);
        $this->em->flush();

        return $user;
    }

    public function refreshUser(UserInterface $user)
    {
        if (!$this->supportsClass(get_class($user))) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }
        
        return $this->_refresh($user);
    }

    private function _refresh(OAuth2User $user)
    {
        $dbToken = $this->em->getRepository(OAuth2AccessToken::class)->findOneBy(['id' => $user->getId()]);
        $accessToken = $dbToken->getAccessToken();

        // Check whether user data is up-to-date (ID token) 
        // and user is logged in (access token)
        if (!$accessToken->getIdToken()->isExpired() && !$accessToken->hasExpired()) {
            return $user;
        }

        // ID token or access token is expired, so user should be refreshed
        try {
            $accessToken = $this->provider->getAccessToken('refresh_token', [
                'refresh_token' => $accessToken->getRefreshToken(),
            ]);

            if ($accessToken->hasExpired())
                throw new AuthenticationExpiredException("Access token has expired");

            // A valid token was obtain, we refresh the user data and return it
            return $this->loadUserByUsername($accessToken);
        } catch (\Exception $e) {
            // getAccessToken throws exception, this means either a problem
            // occurred, or user has been logged out. Therefore, we log out
            $this->em->remove($dbToken);
            $this->em->flush();

            throw new UsernameNotFoundException(); // ugly, symfony fix ur shit
        }
    }

    public function supportsClass($class)
    {
        return OAuth2User::class === $class;
    }

    public function logout(Request $request, Response $response, TokenInterface $token)
    {
        $user = $token->getUser();
        if ($this->supportsClass(get_class($user))) {
            $revokeUrl = ($_ENV['SECURE_SCHEME'] ?? 'https') . '://' . $_ENV['BUNNY_ADDRESS'] . '/revoke';

            $dbToken = $this->em->getRepository(OAuth2AccessToken::class)->find($user->getId());
            $accessToken = $dbToken->getAccessToken();
            $refreshToken = $accessToken->getRefreshToken();

            if (!is_null($accessToken)) {
                if (!is_null($refreshToken)) {
                    $revokeRequest = $this->provider->getAuthenticatedRequest("POST", $revokeUrl, $accessToken, [
                        'body' => "token=" . $refreshToken,
                    ]);
                    $this->provider->getResponse($revokeRequest);
                }

                $revokeRequest = $this->provider->getAuthenticatedRequest("POST", $revokeUrl, $accessToken, [
                    'body' => "token=" . $accessToken,
                ]);
                $this->provider->getResponse($revokeRequest);
            }
        }

        $request->getSession()->invalidate();
    }

    public function onLogoutSuccess(Request $request)
    {
        if (isset($_ENV['BUNNY_ADDRESS']))
            return new RedirectResponse(($_ENV['SECURE_SCHEME'] ?? 'https') . '://' . $_ENV['BUNNY_ADDRESS'] . '/logout');
        else
            return new RedirectResponse('/');
    }
}