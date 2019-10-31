<?php

namespace App\Controller\Admin;

use App\Entity\Group\Category;
use App\Entity\Group\Group;
use App\Template\Annotation\MenuItem;
use App\Entity\Group\Taxonomy;
use App\Entity\Group\Relation;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;

/**
 * Group category controller.
 *
 * @Route("/admin/group", name="admin_group_")
 */
class GroupController extends AbstractController
{
    /**
     * Generate default groups.
     *
     * @Route("/generate", name="generate_default", methods={"GET", "POST"})
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
     * Lists all groups.
     *
     * @MenuItem(title="Groepen", menu="admin")
     * @Route("/{id?}", name="show", methods={"GET"})
     */
    public function showAction(Request $request, ?Taxonomy $taxonomy)
    {
        $em = $this->getDoctrine()->getManager();

        if (!$taxonomy && $request->query->get('showall')) {
            $children = $em->getRepository(Category::class)->findAll();
            $instances = $em->getRepository(Group::class)->findAll();

            return $this->render('admin/group/show.html.twig', [
                'taxonomy' => null,
                'children' => $children,
                'instances' => $instances,
                'relations' => [],
                'show_instances' => true,
            ]);
        }

        $children = $em->getRepository(Category::class)->findBy(['parent' => $taxonomy]);
        $instances = $em->getRepository(Group::class)->findBy(['parent' => $taxonomy]);
        $relations = $em->getRepository(Relation::class)->findBy(['taxonomy' => $taxonomy]);

        return $this->render('admin/group/show.html.twig', [
            'taxonomy' => $taxonomy,
            'children' => $children,
            'instances' => $instances,
            'relations' => $relations,
        ]);
    }

    private function generateStructure($defaultBoard)
    {
        $em = $this->getDoctrine()->getManager();

        $boards = new Category();
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

        $committees = new Category();
        $committees
            ->setName('Commissies')
            ->setHasInstances(false)
        ;
        $em->persist($committees);

        $boards = new Category();
        $boards
            ->setName('Disputen')
        ;
        $em->persist($boards);

        $positions = new Category();
        $positions
            ->setName('Functies')
            ->setHasChildren(false)
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
