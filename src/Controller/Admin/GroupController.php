<?php

namespace App\Controller\Admin;

use App\Entity\Group\Group;
use App\Entity\Security\LocalAccount;
use App\Log\Doctrine\EntityUpdateEvent;
use App\Log\EventService;
use App\Repository\GroupRepository;
use App\Template\Attribute\MenuItem;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Group category controller.
 */
#[Route("/admin/group", name: "admin_group_")]
class GroupController extends AbstractController
{
    public function __construct(
        private EventService $events,
        private EntityManagerInterface $em
    ) {
    }

    /**
     * Creates a new group entity.
     */
    #[Route("/new/{id?}", name: "new", methods: ["GET", "POST"])]
    public function newAction(Request $request, ?Group $parent): Response
    {
        $this->denyAccessUnlessGranted('in_group', $parent);

        $group = new Group();

        $form = $this->createForm('App\Form\Group\GroupType', $group);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($group);

            if ($parent !== null) {
                $parent->addChild($group);
                $this->em->persist($parent);
            }

            $this->em->flush();

            return $this->redirectToRoute('admin_group_show', ['id' => $group->getId()]);
        }

        return $this->render('admin/group/new.html.twig', [
            'group' => $group,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Lists all groups.
     */
    #[MenuItem(title: "Groepen", menu: "admin")]
    #[Route("/{id?}", name: "show", methods: ["GET"])]
    public function showAction(Request $request, ?Group $group, GroupRepository $groupRepo): Response
    {
        /** @var LocalAccount */
        $user = $this->getUser();

        if (null === $group) {
            $this->denyAccessUnlessGranted('any_group');

            // setup basic render parameters
            $params = [
                'group' => null,
                'can_edit' => $this->isGranted('edit_group'),
            ];

            // all groups or only the top level groups
            if ((bool) $request->query->get('showall')) {
                $params['all_groups'] = true;

                if ($this->isGranted('ROLE_ADMIN')) {
                    $db = $groupRepo->findAll();
                } else {
                    $db = $groupRepo->findSubGroupsForPerson($user);
                }
            } else {
                if ($this->isGranted('ROLE_ADMIN')) {
                    $db = $groupRepo->findBy(['parent' => null]);
                } else {
                    $db = $user->getRelations();
                }
            }

            // render
            $params['groups'] = $db;

            return $this->render('admin/group/show.html.twig', $params);
        }

        $this->denyAccessUnlessGranted('in_group', $group);

        return $this->render('admin/group/show.html.twig', [
            'group' => $group,
            'can_edit' => $this->isGranted('edit_group', $group),
            'modifs' => $this->events->findBy($group, EntityUpdateEvent::class),
        ]);
    }

    /**
     * Displays a form to edit an existing group entity.
     */
    #[Route("/{id}/edit", name: "edit", methods: ["GET", "POST"])]
    public function editAction(Request $request, Group $group): Response
    {
        $this->denyAccessUnlessGranted('edit_group', $group);

        $form = $this->createForm('App\Form\Group\GroupType', $group);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();

            return $this->redirectToRoute('admin_group_show', ['id' => $group->getId()]);
        }

        return $this->render('admin/group/edit.html.twig', [
            'group' => $group,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Deletes a ApiKey entity.
     */
    #[Route("/{id}/delete", name: "delete")]
    public function deleteAction(Request $request, Group $group): Response
    {
        $this->denyAccessUnlessGranted('edit_group', $group);

        $form = $this->createDeleteForm($group);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->remove($group);
            $this->em->flush();

            return $this->redirectToRoute('admin_group_show');
        }

        return $this->render('admin/group/delete.html.twig', [
            'group' => $group,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Displays a form to generate a new relation to a group entity.
     */
    #[Route("/relation/new/{id}", name: "relation_new", methods: ["GET", "POST"])]
    public function relationNewAction(Request $request, Group $group): Response
    {
        $this->denyAccessUnlessGranted('edit_group', $group);

        $form = $this->createForm('App\Form\Group\RelationType');
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $account = $form->get('person')->getData();
            assert($account instanceof LocalAccount);

            $group->addRelation($account);

            $this->em->flush();

            $name = $account->getCanonical();
            $this->addFlash('success', $name.' toegevoegd!');

            return $this->redirectToRoute('admin_group_show', ['id' => $group->getId()]);
        }

        return $this->render('admin/group/relation/new.html.twig', [
            'group' => $group,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Deletes a ApiKey entity.
     */
    #[Route("/relation/delete/{id}/{account_id}", name: "relation_delete")]
    #[Entity('account', expr: 'repository.find(account_id)')]
    public function relationDeleteAction(Request $request, Group $relation, LocalAccount $account): Response
    {
        $this->denyAccessUnlessGranted('edit_group', $relation);

        $form = $this->createRelationDeleteForm($relation, $account);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $relation->removeRelation($account);

            $this->em->flush();

            return $this->redirectToRoute('admin_group_show', ['id' => $relation->getId()]);
        }

        return $this->render('admin/group/relation/delete.html.twig', [
            'group' => $relation,
            'account' => $account,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Creates a form to check out all checked in users.
     *
     * @return \Symfony\Component\Form\FormInterface The form
     */
    private function createDeleteForm(Group $group): FormInterface
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_group_delete', ['id' => $group->getId()]))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }

    /**
     * Creates a form to check out all checked in users.
     *
     * @return \Symfony\Component\Form\FormInterface The form
     */
    private function createRelationDeleteForm(Group $group, LocalAccount $account): FormInterface
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_group_relation_delete', ['id' => $group->getId(), 'account_id' => $account->getId()]))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
