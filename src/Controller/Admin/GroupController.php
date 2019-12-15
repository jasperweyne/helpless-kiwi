<?php

namespace App\Controller\Admin;

use App\Template\Annotation\MenuItem;
use App\Entity\Group\Taxonomy;
use App\Entity\Group\Relation;
use SebastianBergmann\Environment\Console;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use App\Log\EventService;
use App\Log\Doctrine\EntityNewEvent;
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
     * Lists all groups.
     *
     * @MenuItem(title="Groepen", menu="admin")
     * @Route("/{id?}", name="show", methods={"GET"})
     */
    public function showAction(Request $request, ?Taxonomy $taxonomy)
    {
        $em = $this->getDoctrine()->getManager();

        

        if (!$taxonomy && $request->query->get('showall')) {
            $children = $em->getRepository(Taxonomy::class)->findAll();
            $instances = null;

            return $this->render('admin/group/show.html.twig', [
                'taxonomy' => null,
                'children' => $children,
                'instances' => $instances,
                'relations' => [],
                'show_instances' => true,
            ]);
        }

        $children = $em->getRepository(Taxonomy::class)->findBy(['parent' => $taxonomy]);
        $relations = $em->getRepository(Relation::class)->findBy(['taxonomy' => $taxonomy]);
        $modifs = $this->events->findBy($taxonomy, EntityUpdateEvent::class);

        return $this->render('admin/group/show.html.twig', [
            'taxonomy' => $taxonomy,
            'children' => $children,
            'modifs' => $modifs,
            'relations' => $relations,
        ]);
    }


    /**
     * Creates a new group entity.
     *
     * @Route("/new/{id?}", name="new", methods={"GET", "POST"})
     */
    public function newAction(Request $request, ?Taxonomy $parent)
    {
        
        $taxonomy = new Taxonomy();
        
        $form = $this->createForm('App\Form\Group\TaxonomyType', $taxonomy);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($taxonomy);
            
            if ($parent) {
                $parent->addTaxonomy($taxonomy);
                $em->persist($parent);

            };
            
            
            $em->flush();

            return $this->redirectToRoute('admin_group_show', ['id' => $taxonomy->getId()]);
        }

        return $this->render('admin/group/new.html.twig', [
            'taxonomy' => $taxonomy,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Displays a form to edit an existing taxonomy entity.
     *
     * @Route("/{id}/edit", name="edit", methods={"GET", "POST"})
     */
    public function editAction(Request $request, Taxonomy $taxonomy)
    {
        $form = $this->createForm('App\Form\Group\TaxonomyType', $taxonomy);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('admin_group_show', ['id' => $taxonomy->getId()]);
        }

        return $this->render('admin/group/edit.html.twig', [
            'taxonomy' => $taxonomy,
            'form' => $form->createView(),
        ]);
    }


    /**
     * Displays a form to generate a new relation to a taxonomy entity.
     *
     * @Route("/relation/new/{id}", name="relation_new", methods={"GET", "POST"})
     */
    public function relationNewAction(Request $request, Taxonomy $taxonomy)
    {
        $em = $this->getDoctrine()->getManager();

        $relation = new Relation();
        $relation->setTaxonomy($taxonomy);


        $form = $this->createForm('App\Form\Group\RelationType', $relation);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($relation);
            $em->flush();

            $this->addFlash('success', $relation->getPerson()->getFullname().' toegevoegd!');

           
            return $this->redirectToRoute('admin_group_show', ['id' => $taxonomy->getId()]);
        }

        return $this->render('admin/group/relation/new.html.twig', [
            'taxonomy' => $taxonomy,
            'form' => $form->createView(),
        ]);
    }


    private function generateStructure($defaultBoard)
    {
        $em = $this->getDoctrine()->getManager();

        $boards = new Taxonomy();
        $boards
            ->setName('Besturen')
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
        ;
        $em->persist($committees);

        $boards2 = new Taxonomy();
        $boards2
            ->setName('Disputen')
        ;
        $em->persist($boards2);

        $positions = new Taxonomy();
        $positions
            ->setName('Functies')   
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
