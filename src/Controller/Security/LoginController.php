<?php

namespace App\Controller\Security;

use App\Security\OAuth2Authenticator;
use App\Template\Annotation\MenuItem;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Contracts\Translation\TranslatorInterface;

class LoginController extends AbstractController
{
    /**
     * @Route("/login_old", name="app_login_old")
     */
    public function login_old(AuthenticationUtils $authenticationUtils, TranslatorInterface $translator): Response
    {
        // you can't login again while you already are, redirect
        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirect('/');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        if ($error) {
            $this->addFlash(
                'error',
                $translator->trans($error->getMessageKey(), $error->getMessageData(), 'security')
            );
        }

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername]);
    }

    /**
     * @Route("/login", name="app_login", methods={"GET"})
     */
    public function login(Request $request, OAuth2Authenticator $authenticator)
    {
        return $authenticator->start($request);
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
