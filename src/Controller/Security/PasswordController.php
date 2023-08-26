<?php

namespace App\Controller\Security;

use App\Entity\Security\LocalAccount;
use App\Security\LocalUserProvider;
use App\Security\PasswordResetService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;

/**
 * Password controller.
 */
#[Route('/password', name: 'password_')]
class PasswordController extends AbstractController
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
        private PasswordResetService $passwordReset,
        private EntityManagerInterface $em
    ) {
    }

    /**
     * Reset password.
     */
    #[Route('/reset/{id}', name: 'reset', methods: ['GET', 'POST'])]
    public function resetAction(LocalAccount $auth, Request $request): Response
    {
        if (!$this->passwordReset->isPasswordRequestTokenValid(
            $auth,
            (string) $request->query->get('token')
        )) {
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
     */
    #[Route('/register/{id}', name: 'register', methods: ['GET', 'POST'])]
    public function registerAction(LocalAccount $auth, Request $request): Response
    {
        $token = $request->query->get('token');
        assert(is_string($token));
        if (!$this->passwordReset->isPasswordRequestTokenValid($auth, $token)) {
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
     */
    #[Route('/request', name: 'request', methods: ['GET', 'POST'])]
    public function requestAction(Request $request, LocalUserProvider $userProvider, MailerInterface $mailer): Response
    {
        $form = $this->createForm('App\Form\Security\PasswordRequestType', []);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var array{email: string} $data */
            $data = $form->getData();
            $mail = $data['email'];

            $localAccount = $this->em->getRepository(LocalAccount::class)->findOneBy(['email' => $mail]);
            if (!$localAccount instanceof LocalAccount) {
                $localAccount = new LocalAccount();
                $localAccount
                    ->setGivenName('')
                    ->setFamilyName('')
                    ->setEmail($mail);

                $this->em->persist($localAccount);
                $this->em->flush();
            }

            try {
                $auth = $userProvider->loadUserByUsername($mail);
                assert($auth instanceof LocalAccount);
                $token = $this->passwordReset->generatePasswordRequestToken($auth);

                $mailer->send((new TemplatedEmail())
                    ->from($_ENV['DEFAULT_FROM'])
                    ->to($localAccount->getEmail())
                    ->subject('Wachtwoord vergeten')
                    ->htmlTemplate('email/resetpassword.html.twig')
                    ->context([
                        'auth' => $auth,
                        'token' => urlencode($token),
                    ])
                );
            } catch (UserNotFoundException) {
                $mailer->send((new TemplatedEmail())
                    ->from($_ENV['DEFAULT_FROM'])
                    ->to($localAccount->getEmail())
                    ->subject('Wachtwoord vergeten')
                    ->htmlTemplate('email/unknownemail.html.twig')
                );
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
    private function handleInvalidToken(LocalAccount $auth): RedirectResponse
    {
        $this->passwordReset->resetPasswordRequestToken($auth);
        $this->addFlash('error', 'Invalid password token.');

        return $this->redirectToRoute('app_login');
    }

    /**
     * Handle a valid auth token.
     */
    private function handleValidToken(
        FormInterface $form,
        LocalAccount $auth,
        string $message
    ): RedirectResponse {
        /** @var array{password: string} $data */
        $data = $form->getData();
        $pass = $data['password'];

        $this->passwordReset->resetPasswordRequestToken($auth, false);
        $auth->setPassword($this->passwordHasher->hashPassword($auth, $pass));

        $this->em->persist($auth);
        $this->em->flush();

        $this->addFlash('success', $message);

        return $this->redirectToRoute('app_login');
    }
}
