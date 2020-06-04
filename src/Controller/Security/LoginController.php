<?php

namespace App\Controller\Security;

use App\Entity\Security\LocalAccount;
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
     * @Route("/login", name="app_login")
     */
    public function login(Request $request, OAuth2Authenticator $authenticator, AuthenticationUtils $authenticationUtils, TranslatorInterface $translator): Response
    {
        // you can't login again while you already are, redirect
        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirect('/');
        }

        $em = $this->getDoctrine()->getManager();
        $bunny = $_ENV['BUNNY_ADDRESS'] != "";
        $local = count($em->getRepository(LocalAccount::class)->findAll()) > 0;
        if (($bunny && !$local) || $request->query->getAlpha('provider') == 'bunny') {
            return $authenticator->start($request);
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

        return $this->render('security/login.html.twig', ['bunny' => $bunny, 'last_username' => $lastUsername]);
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
