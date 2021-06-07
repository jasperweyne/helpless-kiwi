<?php

namespace App\Controller\Security;

use App\Template\Annotation\MenuItem;
use Drenso\OidcBundle\OidcClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     */
    public function login(Request $request, AuthenticationUtils $authenticationUtils, OidcClient $oidc): Response
    {
        // you can't login again while you already are, redirect
        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirect('/');
        }

        $oidcEnabled = isset($_ENV['OIDC_ADDRESS']);
        if ('local' !== $request->query->getAlpha('provider') && $oidcEnabled) {
            return $oidc->generateAuthorizationRedirect(null, ['openid', 'profile', 'email']);
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        if ($error) {
            $this->addFlash(
                'error',
                'Invalid credentials.'
            );
        }

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig',
            ['last_username' => $lastUsername]);
    }

    /**
     * @Route("/login_check", name="app_login_check")
     */
    public function login_check()
    {
        return $this->redirect('/');
    }

    /**
     * @MenuItem(title="Uitloggen", menu="admin-profile", class="mobile")
     * @Route("/logout", name="app_logout", methods={"GET"})
     */
    public function logout()
    {
        // controller can be blank: it will never be executed!
        throw new \Exception('Don\'t forget to activate logout in security.yaml');
    }
}
