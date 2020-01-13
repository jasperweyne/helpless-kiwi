<?php

namespace App\Controller\Admin;

use App\Template\Annotation\MenuItem;
use App\Entity\Group\Group;
use App\Entity\Group\Relation;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use App\Log\EventService;
use App\Log\Doctrine\EntityUpdateEvent;

/**
 * Group category controller.
 *
 * @Route("/admin/group", name="admin_group_")
 */
class GroupController extends AbstractController
{
    private $events;

    public function __construct(EventService $events)
    {
        $this->events = $events;
    }

    /**
     * Generate default groups.
     *
     * @ Route("/generate", name="generate_default", methods={"GET", "POST"})
     */
    public function generateAction(Request $request)
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
    public function editAction(Request $request, Group $group)
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
     * Displays a form to generate a new relation to a group entity.
     *
     * @Route("/relation/new/{id}", name="relation_new", methods={"GET", "POST"})
     */
    public function relationNewAction(Request $request, Group $group)
    {
        $em = $this->getDoctrine()->getManager();

        $relation = new Relation();
        $relation->setGroup($group);

        $form = $this->createForm('App\Form\Group\RelationType', $relation);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($relation);
            $em->flush();

            $this->addFlash('success', $relation->getPerson()->getFullname().' toegevoegd!');

            return $this->redirectToRoute('admin_group_show', ['id' => $group->getId()]);
        }

        return $this->render('admin/group/relation/new.html.twig', [
            'group' => $group,
            'form' => $form->createView(),
        ]);
    }

    private function generateStructure($defaultBoard)
    {
        $em = $this->getDoctrine()->getManager();

        $boards = new Group();
        $boards
            ->setName('Besturen')
        ;
        $em->persist($boards);

        $current = new Group();
        $current
            ->setName($defaultBoard)
            ->setParent($boards)
        ;
        $em->persist($current);

        $committees = new Group();
        $committees
            ->setName('Commissies')
        ;
        $em->persist($committees);

        $boards2 = new Group();
        $boards2
            ->setName('Disputen')
        ;
        $em->persist($boards2);

        $positions = new Group();
        $positions
            ->setName('Functies')
        ;
        $em->persist($positions);

        $president = new Group();
        $president
            ->setName('Voorzitter')
            ->setParent($positions)
        ;
        $em->persist($president);

        $secretary = new Group();
        $secretary
            ->setName('Secretaris')
            ->setParent($positions)
        ;
        $em->persist($secretary);

        $treasurer = new Group();
        $treasurer
            ->setName('Penningmeester')
            ->setParent($positions)
        ;
        $em->persist($treasurer);

        $em->flush();
    }
}
