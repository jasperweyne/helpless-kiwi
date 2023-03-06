<?php

namespace App\Controller\Admin;

use App\Entity\Security\LocalAccount;
use App\Log\Doctrine\EntityNewEvent;
use App\Log\Doctrine\EntityUpdateEvent;
use App\Log\EventService;
use App\Mail\MailService;
use App\Security\PasswordResetService;
use App\Template\Attribute\MenuItem;
use App\Template\Attribute\SubmenuItem;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Security controller.
 */
#[Route("/admin/security", name: "admin_security_")]
class SecurityController extends AbstractController
{
    public function __construct(
        private EventService $events,
        private EntityManagerInterface $em
    ) {
    }

    /**
     * Lists all local account entities.
     */
    #[MenuItem(title: "Accounts", menu: "admin", activeCriteria: "admin_security_", role: "ROLE_ADMIN", sub: [
        new SubmenuItem(title: 'Gebruikers', path: 'admin_security_index'),
        new SubmenuItem(title: 'API', path: 'admin_security_client_index'),
    ])]
    #[Route("/", name: "index", methods: ["GET", "POST"])]
    public function indexAction(Request $request): Response
    {
        $accounts = $this->em->getRepository(LocalAccount::class)->findAll();

        return $this->render('admin/security/index.html.twig', [
            'accounts' => $accounts,
        ]);
    }

    /**
     * Creates a new activity entity.
     */
    #[Route("/new", name: "new", methods: ["GET", "POST"])]
    public function newAction(Request $request, PasswordResetService $passwordReset, MailService $mailer): Response
    {
        $account = new LocalAccount();

        $form = $this->createForm('App\Form\Security\LocalAccountType', $account);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $token = $passwordReset->generatePasswordRequestToken($account);
            $account->setPasswordRequestedAt(null);

            $this->em->persist($account);
            $this->em->flush();

            $body = $this->renderView('email/newaccount.html.twig', [
                'name' => $account->getGivenName(),
                'account' => $account,
                'token' => $token,
            ]);

            $mailer->message($account, 'Jouw account', $body);

            return $this->redirectToRoute('admin_security_show', ['id' => $account->getId()]);
        }

        return $this->render('admin/security/new.html.twig', [
            'account' => $account,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Finds and displays an auth entity.
     */
    #[Route("/{id}", name: "show", methods: ["GET"])]
    public function showAction(LocalAccount $account): Response
    {
        $createdAt = $this->events->findOneBy($account, EntityNewEvent::class);
        $modifs = $this->events->findBy($account, EntityUpdateEvent::class);

        return $this->render('admin/security/show.html.twig', [
            'createdAt' => $createdAt,
            'modifs' => $modifs,
            'account' => $account,
        ]);
    }

    /**
     * Displays a form to edit an existing activity entity.
     */
    #[Route("/{id}/edit", name: "edit", methods: ["GET", "POST"])]
    public function editAction(Request $request, LocalAccount $account): Response
    {
        $form = $this->createForm('App\Form\Security\LocalAccountType', $account);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();

            return $this->redirectToRoute('admin_security_show', ['id' => $account->getId()]);
        }

        return $this->render('admin/activity/edit.html.twig', [
            'account' => $account,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Deletes a ApiKey entity.
     */
    #[Route("/{id}/delete", name: "delete")]
    public function deleteAction(Request $request, LocalAccount $account): Response
    {
        $form = $this->createDeleteForm($account);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->remove($account);
            $this->em->flush();

            return $this->redirectToRoute('admin_security_index');
        }

        return $this->render('admin/activity/delete.html.twig', [
            'account' => $account,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Displays a form to edit roles.
     */
    #[Route("/{id}/roles", name: "roles", methods: ["GET", "POST"])]
    public function rolesAction(Request $request, LocalAccount $account): Response
    {
        $form = $this->createRoleForm($account);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = (array) $form->getData();

            $roles = [];
            if ($data['admin']) {
                $roles[] = 'ROLE_ADMIN';
            }

            $account->setRoles($roles);

            $this->em->persist($account);
            $this->em->flush();

            $this->addFlash('success', 'Rollen bewerkt');

            return $this->redirectToRoute('admin_security_show', ['id' => $account->getId()]);
        }

        return $this->render('admin/security/roles.html.twig', [
            'form' => $form->createView(),
            'auth' => $account,
        ]);
    }

    /**
     * Creates a form to check out all checked in users.
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(LocalAccount $account): FormInterface
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_activity_delete', ['id' => $account->getId()]))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }

    private function createRoleForm(LocalAccount $account): FormInterface
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_security_roles', ['id' => $account->getId()]))
            ->add('admin', CheckboxType::class, [
                'required' => false,
                'attr' => in_array('ROLE_ADMIN', $account->getRoles()) ? ['checked' => 'checked'] : [],
            ])
            ->getForm()
        ;
    }
}
