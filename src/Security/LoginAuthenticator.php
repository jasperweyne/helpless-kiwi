<?php

namespace App\Security;

use App\EventSubscriber\ProfileUpdateSubscriber;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validation;

class LoginAuthenticator extends AbstractFormLoginAuthenticator
{
    use TargetPathTrait;

    private $em;
    private $urlGenerator;
    private $csrfTokenManager;
    private $passwordEncoder;

    public function __construct(EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator, CsrfTokenManagerInterface $csrfTokenManager, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->em = $em;
        $this->urlGenerator = $urlGenerator;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->passwordEncoder = $passwordEncoder;
    }

    public function supports(Request $request)
    {
        return 'app_login' === $request->attributes->get('_route')
            && $request->isMethod('POST');
    }

    public function getCredentials(Request $request)
    {
        $credentials = [
            'username' => trim($request->request->get('username')),
            'password' => $request->request->get('password'),
            'csrf_token' => $request->request->get('_csrf_token'),
        ];
        $request->getSession()->set(
            Security::LAST_USERNAME,
            $credentials['username']
        );

        return $credentials;
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $token = new CsrfToken('authenticate', $credentials['csrf_token']);
        if (!$this->csrfTokenManager->isTokenValid($token)) {
            throw new InvalidCsrfTokenException();
        }

        // Check if valid email
        $validator = Validation::createValidator();
        $violations = $validator->validate($credentials['username'], new Assert\Email());
        if (0 !== count($violations)) {
            throw new CustomUserMessageAuthenticationException($violations[0]->getMessage());
        }

        if (!$userProvider instanceof AuthUserProvider) {
            throw new \LogicException('User provider not supported!');
        }

        // Load / create our user however you need.
        // You can do this by calling the user provider, or with custom logic here.
        $user = $userProvider->loadUserByEmail($credentials['username']);

        if (!$user) {
            // fail authentication with a custom error
            throw new CustomUserMessageAuthenticationException('Username could not be found.');
        }

        return $user;
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        return $this->passwordEncoder->isPasswordValid($user, $credentials['password']);
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

    protected function getLoginUrl()
    {
        return $this->urlGenerator->generate('app_login');
    }
}
