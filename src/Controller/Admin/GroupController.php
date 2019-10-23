<?php

namespace App\Controller\Admin;

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
    public function showAction(?Taxonomy $taxonomy)
    {
        $em = $this->getDoctrine()->getManager();

        $children = $em->getRepository(Taxonomy::class)->findBy(['parent' => $taxonomy, 'category' => true]);
        $instances = $em->getRepository(Taxonomy::class)->findBy(['parent' => $taxonomy, 'category' => false]);
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

        $boards = new Taxonomy();
        $boards
            ->setName('Besturen')
            ->setCategory(true)
        ;
        $em->persist($boards);

        $current = new Taxonomy();
        $current
            ->setName($defaultBoard)
            ->setParent($boards)
        ;
        $em->persist($current);

        $committees = new Taxonomy();
        $committees
            ->setName('Commissies')
            ->setCategory(true)
        ;
        $em->persist($committees);

        $boards = new Taxonomy();
        $boards
            ->setName('Disputen')
            ->setCategory(true)
        ;
        $em->persist($boards);

        $positions = new Taxonomy();
        $positions
            ->setName('Functies')
            ->setCategory(true)
        ;
        $em->persist($positions);

        $president = new Taxonomy();
        $president
            ->setName('Voorzitter')
            ->setParent($positions)
        ;
        $em->persist($president);

        $secretary = new Taxonomy();
        $secretary
            ->setName('Secretaris')
            ->setParent($positions)
        ;
        $em->persist($secretary);

        $treasurer = new Taxonomy();
        $treasurer
            ->setName('Penningmeester')
            ->setParent($positions)
        ;
        $em->persist($treasurer);

        $em->flush();
    }
}
