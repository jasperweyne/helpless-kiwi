<?php

namespace App\Controller\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Security\Auth;
use App\Security\AuthUserProvider;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use App\Security\PasswordResetService;

/**
 * Password controller.
 *
 * @Route("/password", name="password_")
 */
class PasswordController extends AbstractController
{
    private $passwordEncoder;

    private $passwordReset;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder, PasswordResetService $passwordReset)
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->passwordReset = $passwordReset;
    }

    /**
     * Reset password.
     *
     * @Route("/reset/{id}", name="reset", methods={"GET", "POST"})
     */
    public function resetAction(Auth $auth, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        if (!$this->passwordReset->isPasswordRequestTokenValid($auth, $request->query->get('token'))) {
            $this->passwordReset->resetPasswordRequestToken($auth);

            $this->addFlash('error', 'Invalid password token.');

            return $this->redirectToRoute('app_login');
        }

        $form = $this->createForm('App\Form\Security\NewPasswordType', []);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $pass = $data['password'];

            $this->passwordReset->resetPasswordRequestToken($auth, false);
            $auth->setPassword($this->passwordEncoder->encodePassword($auth, $pass));

            $em->persist($auth);
            $em->flush();

            $this->addFlash('success', 'Wachtwoord aangepast!');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/reset.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Register new password for account.
     *
     * @Route("/register/{id}", name="register", methods={"GET", "POST"})
     */
    public function registerAction(Auth $auth, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        if (!$this->passwordReset->isPasswordRequestTokenValid($auth, $request->query->get('token'))) {
            $this->passwordReset->resetPasswordRequestToken($auth);

            $this->addFlash('error', 'Invalid password token.');

            return $this->redirectToRoute('app_login');
        }

        $form = $this->createForm('App\Form\Security\NewPasswordType', []);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $pass = $data['password'];

            $this->passwordReset->resetPasswordRequestToken($auth, false);
            $auth->setPassword($this->passwordEncoder->encodePassword($auth, $pass));

            $em->persist($auth);
            $em->flush();

            $this->addFlash('success', 'Account succesvol geregistreerd, log in!');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Request new password mail.
     *
     * @Route("/request", name="request", methods={"GET", "POST"})
     */
    public function requestAction(Request $request, AuthUserProvider $userProvider, \Swift_Mailer $mailer)
    {
        $em = $this->getDoctrine()->getManager();

        $form = $this->createForm('App\Form\Security\PasswordRequestType', []);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $mail = $data['email'];

            try {
                $auth = $userProvider->loadUserByUsername($mail);
                $token = $this->passwordReset->generatePasswordRequestToken($auth);

                $body = $this->renderView('email/resetpassword.html.twig', [
                    'auth' => $auth,
                    'token' => $token,
                ]);

                $message = (new \Swift_Message('Wachtwoord vergeten'))
                    ->setFrom($_ENV['DEFAULT_FROM'])
                    ->setTo($mail)
                    ->setBody($body, 'text/html')
                    ->addPart(html_entity_decode(strip_tags($body)), 'text/plain')
                ;

                $mailer->send($message);
            } catch (UsernameNotFoundException $exception) {
                $body = $this->renderView('email/unknownemail.html.twig');

                $message = (new \Swift_Message('Wachtwoord vergeten'))
                    ->setFrom($_ENV['DEFAULT_FROM'])
                    ->setTo($mail)
                    ->setBody($body, 'text/html')
                    ->addPart(html_entity_decode(strip_tags($body)), 'text/plain')
                ;
            }

            $this->addFlash('success', 'Er is een mail met insctructies gestuurd naar ' . $mail);

            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/request.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
