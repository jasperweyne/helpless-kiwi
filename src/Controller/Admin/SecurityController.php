<?php

namespace App\Controller\Admin;

use App\Entity\Security\LocalAccount;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Log\EventService;
use App\Log\Doctrine\EntityNewEvent;
use App\Log\Doctrine\EntityUpdateEvent;
use App\Mail\MailService;
use App\Security\LocalUserProvider;
use App\Security\PasswordResetService;
use App\Template\MenuExtensionInterface;
use Doctrine\DBAL\Types\BooleanType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\HttpFoundation\Request;

/**
 * Security controller.
 *
 * @Route("/admin/security", name="admin_security_")
 */
class SecurityController extends AbstractController implements MenuExtensionInterface
{
    private $events;

    public function __construct(EventService $events)
    {
        $this->events = $events;
    }

    public function getMenuItems(string $menu)
    {
        if ($menu != 'admin')
            return [];

        $em = $this->getDoctrine()->getManager();
        if (isset($_ENV['BUNNY_ADDRESS']) && count($em->getRepository(LocalAccount::class)->findAll()) == 0)
            return [];

        return [[
            'title' => "Accounts",
            'activeCriteria' => "admin_security_",
            'path' => 'admin_security_index',
        ]];
    }

    /**
     * Lists all local account entities.
     *
     * @Route("/", name="index", methods={"GET", "POST"})
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $accounts = $em->getRepository(LocalAccount::class)->findAll();

        return $this->render('admin/security/index.html.twig', [
            'accounts' => $accounts,
        ]);
    }

    /**
     * Creates a new activity entity.
     *
     * @Route("/new", name="new", methods={"GET", "POST"})
     */
    public function newAction(Request $request, PasswordResetService $passwordReset, MailService $mailer)
    {
        $account = new LocalAccount();

        $form = $this->createForm('App\Form\Security\LocalAccountType', $account);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $token = $passwordReset->generatePasswordRequestToken($account);
            $account->setPasswordRequestedAt(null);

            $em->persist($account);
            $em->flush();

            $body = $this->renderView('email/newaccount.html.twig', [
                'name' => $account->getPerson()->getShortname(),
                'account' => $account,
                'token' => $token,
            ]);

            $mailer->message($account->getPerson(), 'Jouw account', $body);

            return $this->redirectToRoute('admin_security_show', ['id' => $account->getId()]);
        }

        return $this->render('admin/security/new.html.twig', [
            'account' => $account,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Finds and displays an auth entity.
     *
     * @Route("/{id}", name="show", methods={"GET"})
     */
    public function showAction(LocalAccount $account)
    {
        $em = $this->getDoctrine()->getManager();

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
     *
     * @Route("/{id}/edit", name="edit", methods={"GET", "POST"})
     */
    public function editAction(Request $request, LocalAccount $account)
    {
        $form = $this->createForm('App\Form\Security\LocalAccountType', $account);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('admin_security_show', ['id' => $account->getId()]);
        }

        return $this->render('admin/activity/edit.html.twig', [
            'account' => $account,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Deletes a ApiKey entity.
     *
     * @Route("/{id}/delete", name="delete")
     */
    public function deleteAction(Request $request, LocalAccount $account)
    {
        $form = $this->createDeleteForm($account);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($account);
            $em->flush();

            return $this->redirectToRoute('admin_security_index');
        }

        return $this->render('admin/activity/delete.html.twig', [
            'account' => $account,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Displays a form to edit roles.
     *
     * @Route("/{id}/roles", name="roles", methods={"GET", "POST"})
     */
    public function rolesAction(Request $request, LocalAccount $account)
    {
        $em = $this->getDoctrine()->getManager();

        $form = $this->createRoleForm($account);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $roles = array();
            if ($data['admin']) {
                $roles[] = "ROLE_ADMIN";
            }

            $account->setRoles($roles);

            $em->persist($account);
            $em->flush();
            
            $this->addFlash('success', 'Rollen bewerkt');
            return $this->redirectToRoute('admin_security_show', ['person' => $account->getPerson()->getId()]);
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
    private function createDeleteForm(LocalAccount $account)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_activity_delete', ['id' => $account->getId()]))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }

    private function createRoleForm(LocalAccount $account)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_security_roles', ['id' => $account->getId()]))
            ->add('admin', CheckboxType::class, [
                'required' => false,
                'attr' => in_array("ROLE_ADMIN", $account->getRoles()) ? ['checked' => 'checked'] : [],
            ])
            ->getForm()
        ;
    }
}
