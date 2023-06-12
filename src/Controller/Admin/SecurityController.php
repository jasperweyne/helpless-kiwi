<?php

namespace App\Controller\Admin;

use App\Entity\Security\LocalAccount;
use App\Event\Security\CreateAccountsEvent;
use App\Event\Security\RemoveAccountsEvent;
use App\Form\Security\Import\ImportAccountsFlow;
use App\Form\Security\Import\ImportedAccounts;
use App\Log\Doctrine\EntityNewEvent;
use App\Log\Doctrine\EntityUpdateEvent;
use App\Log\EventService;
use App\Template\Attribute\MenuItem;
use App\Template\Attribute\SubmenuItem;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Security controller.
 */
#[Route('/admin/security', name: 'admin_security_')]
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
    #[MenuItem(title: 'Accounts', menu: 'admin', activeCriteria: 'admin_security_', role: 'ROLE_ADMIN', sub: [
        new SubmenuItem(title: 'Gebruikers', path: 'admin_security_index'),
        new SubmenuItem(title: 'API', path: 'admin_security_client_index'),
    ])]
    #[Route('/', name: 'index', methods: ['GET', 'POST'])]
    public function indexAction(): Response
    {
        $accounts = $this->em->getRepository(LocalAccount::class)->findAll();

        return $this->render('admin/security/index.html.twig', [
            'accounts' => $accounts,
        ]);
    }

    /**
     * Import multiple accounts, overriding the current list of accoutns.
     */
    #[Route('/import', name: 'import', methods: ['GET', 'POST'])]
    public function importAction(ImportAccountsFlow $flow, EventDispatcherInterface $dispatcher): Response
    {
        $accounts = $this->em->getRepository(LocalAccount::class)->findAll();
        $formData = new ImportedAccounts($accounts);

        $flow->bind($formData);
        $form = $flow->createForm();

        if ($flow->isValid($form)) {
            $flow->saveCurrentStepData($form);

            if ($flow->nextStep()) {
                // form for the next step
                $form = $flow->createForm();
            } else {
                // flow finished
                $formData->executeImport($dispatcher, $this->em);

                $flow->reset(); // remove step data from the session

                $this->addFlash('success', 'Accounts succesvol geimporteerd');

                return $this->redirectToRoute('admin_security_index');
            }
        }

        return $this->render('admin/security/import.html.twig', [
            'form' => $form->createView(),
            'flow' => $flow,
            'data' => $formData,
        ]);
    }

    /**
     * Creates a new LocalAccount.
     */
    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function newAction(Request $request, EventDispatcherInterface $dispatcher): Response
    {
        $account = new LocalAccount();

        $form = $this->createForm('App\Form\Security\LocalAccountType', $account);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $dispatcher->dispatch(new CreateAccountsEvent([$account]));

            return $this->redirectToRoute('admin_security_show', ['id' => $account->getId()]);
        }

        return $this->render('admin/security/new.html.twig', [
            'account' => $account,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Show selected LocalAccount.
     */
    #[Route('/{id}', name: 'show', methods: ['GET'])]
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
     * Edit selected LocalAccount.
     */
    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function editAction(Request $request, LocalAccount $account): Response
    {
        $form = $this->createForm('App\Form\Security\LocalAccountType', $account);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();

            return $this->redirectToRoute('admin_security_show', ['id' => $account->getId()]);
        }

        return $this->render('admin/security/edit.html.twig', [
            'account' => $account,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Delete selected LocalAccount.
     */
    #[Route('/{id}/delete', name: 'delete')]
    public function deleteAction(Request $request, LocalAccount $account, EventDispatcherInterface $dispatcher): Response
    {
        $form = $this->createDeleteForm($account);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $dispatcher->dispatch(new RemoveAccountsEvent([$account]));

            return $this->redirectToRoute('admin_security_index');
        }

        return $this->render('admin/security/delete.html.twig', [
            'account' => $account,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Edit roles for selected LocalAccount.
     */
    #[Route('/{id}/roles', name: 'roles', methods: ['GET', 'POST'])]
    public function rolesAction(Request $request, LocalAccount $account): Response
    {
        $form = $this->createRoleForm($account);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = (array) $form->getData();

            $roles = [];
            if (true === $data['admin']) {
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
     * Creates a form to delete an LocalAccount.
     *
     * @return \Symfony\Component\Form\FormInterface The form
     */
    private function createDeleteForm(LocalAccount $account): FormInterface
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_security_delete', ['id' => $account->getId()]))
            ->setMethod('DELETE')
            ->getForm();
    }

    /**
     * Creates a form to edit a LocalAccounts roles.
     *
     * @return \Symfony\Component\Form\FormInterface The form
     */
    private function createRoleForm(LocalAccount $account): FormInterface
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_security_roles', ['id' => $account->getId()]))
            ->add('admin', CheckboxType::class, [
                'required' => false,
                'attr' => in_array('ROLE_ADMIN', $account->getRoles(), true) ? ['checked' => 'checked'] : [],
            ])
            ->getForm();
    }
}
