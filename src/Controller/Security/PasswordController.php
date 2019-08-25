<?php

namespace App\Controller\Security;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Security\Auth;
use App\Security\AuthUserProvider;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

/**
 * Password controller.
 *
 * @Route("/password", name="password_")
 */
class PasswordController extends AbstractController
{
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    /**
     * Edit forgotten password.
     *
     * @Route("/forgot/{token}", name="forgot", methods={"GET", "POST"})
     */
    public function forgotAction(string $token)
    {
        $em = $this->getDoctrine()->getManager();

        $auth = $em->getRepository(Auth::class)->findByConfirmationToken($token);

        if (null === $auth || !$auth->isPasswordRequestNonExpired(mktime(0, 0, 0, 0, 1, 0, 0))) {
            $this->addFlash('error', 'Invalid password token.');

            return $this->redirectToRoute('app_login');
        }

        $form = $this->createForm('App\Form\Security\PasswordRegisterType', $activity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $pass = $data['password'];

            $auth
                ->setPassword($this->passwordEncoder->encodePassword($auth, $pass))
                ->setPasswordRequestedAt(null)
                ->setConfirmationToken(null)
            ;

            $em->persist($auth);
            $em->flush();

            $this->addFlash('success', 'Wachtwoord aangepast!');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('password/forgot.html.twig');
    }

    /**
     * Register new password for account.
     *
     * @Route("/register/{token}", name="register", methods={"GET", "POST"})
     */
    public function registerAction(UserPasswordEncoderInterface $passwordEncoder, string $token)
    {
        $em = $this->getDoctrine()->getManager();

        $auth = $em->getRepository(Auth::class)->findByConfirmationToken($token);

        if (null === $auth || !$auth->isPasswordRequestNonExpired(mktime(0, 0, 0, 0, 1, 0, 0))) {
            $this->addFlash('error', 'Invalid password token.');

            return $this->redirectToRoute('app_login');
        }

        $form = $this->createForm('App\Form\Security\PasswordRegisterType', $activity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $pass = $data['password'];

            $auth
                ->setPassword($this->passwordEncoder->encodePassword($auth, $pass))
                ->setPasswordRequestedAt(null)
                ->setConfirmationToken(null)
            ;

            $em->persist($auth);
            $em->flush();

            $this->addFlash('success', 'Account succesvol geregistreerd, log in!');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('password/register.html.twig');
    }

    /**
     * Request new password mail.
     *
     * @Route("/request", name="request", methods={"GET", "POST"})
     */
    public function requestAction(AuthUserProvider $userProvider)
    {
        $em = $this->getDoctrine()->getManager();

        $form = $this->createForm('App\Form\Security\PasswordRequestType', $activity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            try {
                $auth = $userProvider->loadUserByUsername($data['email']);
                $auth
                    ->setPasswordRequestedAt(new \DateTime())
                    ->setConfirmationToken()
                ;

                $em->persist($auth);
                $em->flush();

                // todo: send email to
            } catch (UsernameNotFoundException $exception) {
            }

            $this->addFlash('success', 'Indien bij ons een account bekend is, is er een herstelmail gestuurd!');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('password/request.html.twig');
    }
}
