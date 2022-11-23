<?php

namespace App\Controller\Admin;

use App\Entity\Group\Group;
use App\Entity\Group\Relation;
use App\Entity\Security\LocalAccount;
use App\Log\Doctrine\EntityUpdateEvent;
use App\Log\EventService;
use App\Repository\GroupRepository;
use App\Template\Attribute\MenuItem;
use Doctrine\ORM\EntityManagerInterface;
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

            if ($parent) {
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
                    $db = $groupRepo->findAllFor($user);
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

        $relation = new Relation();
        $relation->setGroup($group);

        $form = $this->createForm('App\Form\Group\RelationType', $relation);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($relation);
            $this->em->flush();

            $name = $relation->getPerson()->getCanonical();
            $this->addFlash('success', $name.' toegevoegd!');

            return $this->redirectToRoute('admin_group_show', ['id' => $group->getId()]);
        }

        return $this->render('admin/group/relation/new.html.twig', [
            'group' => $group,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Displays a form to generate a new relation to a group entity.
     */
    #[Route("/relation/add/{id}", name: "relation_add", methods: ["GET", "POST"])]
    public function relationAddAction(Request $request, Relation $parent): Response
    {
        $this->denyAccessUnlessGranted('edit_group', $parent->getGroup());

        $relation = new Relation();

        $form = $this->createForm('App\Form\Group\RelationAddType', $relation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($relation->getAllRelations()->exists(function ($x, $group) use ($relation) {
                return $group->getId() === $relation->getGroup()->getId();
            })) {
                $this->addFlash('error', $relation->getGroup()->getName().' al in deze relatie!');

                return $this->render('admin/group/relation/add.html.twig', [
                    'relation' => $parent,
                    'form' => $form->createView(),
                ]);
            }

            $root = $parent->getRoot();
            $relation->setParent($root);

            $this->em->persist($relation);
            $this->em->flush();

            $this->addFlash('success', $relation->getGroup()->getName().' toegevoegd!');

            return $this->redirectToRoute('admin_group_show', ['id' => $parent->getGroup()->getId()]);
        }

        return $this->render('admin/group/relation/add.html.twig', [
            'relation' => $parent,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Deletes a ApiKey entity.
     */
    #[Route("/relation/delete/{id}", name: "relation_delete")]
    public function relationDeleteAction(Request $request, Relation $relation): Response
    {
        $this->denyAccessUnlessGranted('edit_group', $relation->getGroup());

        $form = $this->createRelationDeleteForm($relation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->remove($relation);
            $this->em->flush();

            return $this->redirectToRoute('admin_group_show', ['id' => $relation->getGroup()->getId()]);
        }

        return $this->render('admin/group/relation/delete.html.twig', [
            'relation' => $relation,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Creates a form to check out all checked in users.
     *
     * @return \Symfony\Component\Form\Form The form
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
     * @return \Symfony\Component\Form\Form The form
     */
    private function createRelationDeleteForm(Relation $group): FormInterface
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_group_relation_delete', ['id' => $group->getId()]))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
