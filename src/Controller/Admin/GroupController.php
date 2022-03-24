<?php

namespace App\Controller\Admin;

use App\Entity\Group\Group;
use App\Entity\Group\Relation;
use App\Log\Doctrine\EntityUpdateEvent;
use App\Log\EventService;
use App\Template\Annotation\MenuItem;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Group category controller.
 *
 * @Route("/admin/group", name="admin_group_")
 */
class GroupController extends AbstractController
{
    /**
     * @var EventService
     */
    private $events;

    public function __construct(EventService $events)
    {
        $this->events = $events;
    }

    /**
     * Generate default groups.
     * It does require the user to create at least one board.
     *
     * @Route("/generate", name="generate_default", methods={"GET", "POST"})
     */
    public function generateAction(Request $request): Response
    {
        $form = $this->createFormBuilder()
            ->add('board', TextType::class, [
                'label' => 'Bestuur',
                'required' => true,
                'attr' => [
                    'placeholder' => 'Bestuur xx: xxxx',
                ],
            ])
            ->getForm()
        ;
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $this->generateStructure($data['board']);

            $this->addFlash('success', 'Standaard groepen gegenereerd, begin met invullen!');

            return $this->redirectToRoute('admin_group_show');
        }

        return $this->render('admin/group/generate.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Creates a new group entity.
     *
     * @Route("/new/{id?}", name="new", methods={"GET", "POST"})
     */
    public function newAction(Request $request, ?Group $parent)
    {
        $group = new Group();

        $form = $this->createForm('App\Form\Group\GroupType', $group);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($group);

            if ($parent) {
                $parent->addChild($group);
                $em->persist($parent);
            }

            $em->flush();

            return $this->redirectToRoute('admin_group_show', ['id' => $group->getId()]);
        }

        return $this->render('admin/group/new.html.twig', [
            'group' => $group,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Lists all groups.
     *
     * @MenuItem(title="Groepen", menu="admin")
     * @Route("/{id?}", name="show", methods={"GET"})
     */
    public function showAction(Request $request, ?Group $group)
    {
        $em = $this->getDoctrine()->getManager();

        if (!$group) {
            if ($request->query->get('showall')) {
                return $this->render('admin/group/show.html.twig', [
                    'group' => null,
                    'all_groups' => true,
                    'groups' => $em->getRepository(Group::class)->findAll(),
                ]);
            } else {
                return $this->render('admin/group/show.html.twig', [
                    'group' => null,
                    'groups' => $em->getRepository(Group::class)->findBy(['parent' => null]),
                ]);
            }
        }

        return $this->render('admin/group/show.html.twig', [
            'group' => $group,
            'modifs' => $this->events->findBy($group, EntityUpdateEvent::class),
        ]);
    }

    /**
     * Displays a form to edit an existing group entity.
     *
     * @Route("/{id}/edit", name="edit", methods={"GET", "POST"})
     */
    public function editAction(Request $request, Group $group): Response
    {
        $form = $this->createForm('App\Form\Group\GroupType', $group);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('admin_group_show', ['id' => $group->getId()]);
        }

        return $this->render('admin/group/edit.html.twig', [
            'group' => $group,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Deletes a ApiKey entity.
     *
     * @Route("/{id}/delete", name="delete")
     */
    public function deleteAction(Request $request, Group $group): Response
    {
        $form = $this->createDeleteForm($group);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($group);
            $em->flush();

            return $this->redirectToRoute('admin_group_show');
        }

        return $this->render('admin/group/delete.html.twig', [
            'group' => $group,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Displays a form to generate a new relation to a group entity.
     *
     * @Route("/relation/new/{id}", name="relation_new", methods={"GET", "POST"})
     */
    public function relationNewAction(Request $request, Group $group): Response
    {
        $em = $this->getDoctrine()->getManager();

        $relation = new Relation();
        $relation->setGroup($group);

        $form = $this->createForm('App\Form\Group\RelationType', $relation);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($relation);
            $em->flush();

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
     *
     * @Route("/relation/add/{id}", name="relation_add", methods={"GET", "POST"})
     */
    public function relationAddAction(Request $request, Relation $parent): Response
    {
        $em = $this->getDoctrine()->getManager();

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

            $em->persist($relation);
            $em->flush();

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
     *
     * @Route("/relation/delete/{id}", name="relation_delete")
     */
    public function relationDeleteAction(Request $request, Relation $relation): Response
    {
        $form = $this->createRelationDeleteForm($relation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($relation);
            $em->flush();

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
    private function createDeleteForm(Group $group)
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
    private function createRelationDeleteForm(Relation $group)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_group_relation_delete', ['id' => $group->getId()]))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }

    private function generateStructure($defaultBoard)
    {
        $em = $this->getDoctrine()->getManager();

        $boards = new Group();
        $boards
            ->setName('Besturen')
            ->setSubgroupable(true)
        ;
        $em->persist($boards);

        $current = new Group();
        $current
            ->setName($defaultBoard)
            ->setParent($boards)
            ->setRelationable(true)
            ->setActive(true)
        ;
        $em->persist($current);

        $committees = new Group();
        $committees
            ->setName('Commissies')
            ->setSubgroupable(true)
        ;
        $em->persist($committees);

        $boards2 = new Group();
        $boards2
            ->setName('Disputen')
            ->setSubgroupable(true)
        ;
        $em->persist($boards2);

        $positions = new Group();
        $positions
            ->setName('Functies')
            ->setSubgroupable(true)
        ;
        $em->persist($positions);

        $president = new Group();
        $president
            ->setName('Voorzitter')
            ->setParent($positions)
            ->setRelationable(true)
        ;
        $em->persist($president);

        $secretary = new Group();
        $secretary
            ->setName('Secretaris')
            ->setParent($positions)
            ->setRelationable(true)
        ;
        $em->persist($secretary);

        $treasurer = new Group();
        $treasurer
            ->setName('Penningmeester')
            ->setParent($positions)
            ->setRelationable(true)
        ;
        $em->persist($treasurer);

        $em->flush();
    }
}
