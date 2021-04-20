<?php

namespace App\Controller\Security;

use App\Entity\Person\Person;
use App\Entity\Security\LocalAccount;
use App\Mail\MailService;
use App\Security\LocalUserProvider;
use App\Security\PasswordResetService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

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
    public function resetAction(LocalAccount $auth, Request $request)
    {
        if (!$this->passwordReset->isPasswordRequestTokenValid($auth, $request->query->get('token'))) {
            $this->handleInvalidToken($auth);
        }

        $form = $this->createForm('App\Form\Security\NewPasswordType', []);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->handleValidToken($form, $auth, 'Wachtwoord aangepast!');
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
    public function registerAction(LocalAccount $auth, Request $request)
    {
        if (!$this->passwordReset->isPasswordRequestTokenValid($auth, $request->query->get('token'))) {
            $this->handleInvalidToken($auth);
        }

        $form = $this->createForm('App\Form\Security\NewPasswordType', []);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $succesMessage = 'Account succesvol geregistreed, log in!';
            $this->handleValidToken($form, $auth, $succesMessage);
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
    public function requestAction(Request $request, LocalUserProvider $userProvider, MailService $mailer)
    {
        $em = $this->getDoctrine()->getManager();

        $form = $this->createForm('App\Form\Security\PasswordRequestType', []);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $mail = $data['email'];

            $person = $em->getRepository(Person::class)->findOneBy(['email' => $mail]);
            if (!$person) {
                $person = new Person();
                $person
                    ->setEmail($mail)
                ;

                $em->persist($person);
                $em->flush();
            }

            try {
                $auth = $userProvider->loadUserByUsername($mail);
                $token = $this->passwordReset->generatePasswordRequestToken($auth);

                $body = $this->renderView('email/resetpassword.html.twig', [
                    'auth' => $auth,
                    'token' => $token,
                ]);

                $mailer->message($person, 'Wachtwoord vergeten', $body);
            } catch (UsernameNotFoundException $exception) {
                $body = $this->renderView('email/unknownemail.html.twig');

                $mailer->message($person, 'Wachtwoord vergeten', $body);
            }

            $this->addFlash('success', 'Er is een mail met instructies gestuurd naar '.$mail);

            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/request.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Handle an invalid auth token.
     */
    private function handleInvalidToken(LocalAccount $auth)
    {
        $this->passwordReset->resetPasswordRequestToken($auth);
        $this->addFlash('error', 'Invalid password token.');

        return $this->redirectToRoute('app_login');
    }

    /**
     * Handle a valid auth token.
     */
    private function handleValidToken($form, LocalAccount $auth, string $message)
    {
        $data = $form->getData();
        $pass = $data['password'];

        $this->passwordReset->resetPasswordRequestToken($auth, false);
        $auth->setPassword($this->passwordEncoder->encodePassword($auth, $pass));

        $em = $this->getDoctrine()->getManager();
        $em->persist($auth);
        $em->flush();

        $this->addFlash('success', $message);

        return $this->redirectToRoute('app_login');
    }
}
