<?php

namespace App\Controller\Admin\Document;

use App\Entity\Document\Document;
use App\Entity\Person\Person;
use App\Entity\Document\Scheme\Scheme;
use App\Entity\Document\Field\Field;
use App\Entity\Document\Field\Expression;

use App\Entity\Document\Scheme\SchemeDefault;
use App\Log\Doctrine\EntityNewEvent;
use App\Log\Doctrine\EntityUpdateEvent;
use App\Log\EventService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Template\Annotation\MenuItem;
use App\Entity\Inventory\Item;


/**
 * Scheme controller.
 *
 * @Route("/admin/document/scheme", name="admin_document_scheme_")
 */
class SchemeController extends AbstractController
{
    private $events;

    public function __construct(EventService $events)
    {
        $this->events = $events;
    }


    /**
     * Lists all schemas.
     *
     * @MenuItem(title="Schemas", menu="admin", activeCriteria="admin_document_scheme_")
     * @Route("/", name="index", methods={"GET", "POST"})
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        
        $schemes = $em->getRepository(Scheme::class)->findAll();
        $schemeDefaults = $em->getRepository(SchemeDefault::class)->findAll();

        return $this->render('admin/document/scheme/index/index.html.twig', [
            'schemes' => $schemes,
            'defaults' => $schemeDefaults,
        ]);
    }

    /**
     * Creates a new scheme entity.
     *
     * @Route("/new", name="new", methods={"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $scheme = new Scheme();

        $defaults = $em->getRepository(SchemeDefault::class)->findAll();
        $form = $this->createForm('App\Form\Document\Scheme\SchemeType', $scheme, ['defaults' => $defaults]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($scheme);
            $em->flush();

            return $this->redirectToRoute('admin_person_index');
        }

        return $this->render('admin/document/scheme/new.html.twig', [
            'scheme' => $scheme,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Creates a new scheme entity from a selected type.
     *
     * @Route("/new", name="new_selected", methods={"GET", "POST"})
     */
    public function newSelectAction(Request $request,SchemeDefault $default)
    {
        $em = $this->getDoctrine()->getManager();

        $scheme = new Scheme();
        $scheme->setSchemeDefault($default);
        $form = $this->createForm('App\Form\Document\Scheme\SchemeDefaultSelectorType', $scheme);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($scheme);
            $em->flush();

            return $this->redirectToRoute('admin_document_scheme_index');
        }

        return $this->render('admin/document/scheme/new.html.twig', [
            'scheme' => $scheme,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Finds and displays a person entity.
     *
     * @Route("/null", name="null", methods={"GET", "POST"})
     */
    public function nullAction()
    {
        $em = $this->getDoctrine()->getManager();

        $persons = $em->getRepository(Person::class)->findBy(['scheme' => null]);

        return $this->render('admin/document/scheme/null.html.twig', [
            'persons' => $persons,
        ]);
    }

    /**
     * Finds and displays a scheme entity.
     *
     * @Route("/{id}", name="show", methods={"GET", "POST"})
     */
    public function showAction(Scheme $scheme)
    {
        $em = $this->getDoctrine()->getManager();

        $documents = $em->getRepository(Document::class)->findBy(['scheme' => $scheme->getId()]);
        //fix this at some point, need person repository function that finds person array by doc array.
        //$objects = $documents->array_map(function($x) {$x.get } ) 
        //$em->getRepository(Person::class)->findAll();

        $createdAt = $this->events->findOneBy($scheme, EntityNewEvent::class);
        $modifs = $this->events->findBy($scheme, EntityUpdateEvent::class);

        return $this->render('admin/document/scheme/show.html.twig', [
            'objects' => $documents,
            'createdAt' => $createdAt,
            'modifs' => $modifs,
            'scheme' => $scheme,
        ]);
    }

    /**
     * Displays a form to edit an existing scheme entity.
     *
     * @Route("/{id}/edit", name="edit", methods={"GET", "POST"})
     */
    public function editAction(Request $request, Scheme $scheme)
    {
        $em = $this->getDoctrine()->getManager();
        $defaults = $em->getRepository(SchemeDefault::class)->findBy(['schemeType'=>'person']);

        
        $form = $this->createForm('App\Form\Document\Scheme\SchemeType', $scheme, ['defaults' => $defaults]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            return $this->redirectToRoute('admin_person_index');
        }

        return $this->render('admin/document/scheme/edit.html.twig', [
            'scheme' => $scheme,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Deletes a scheme entity.
     *
     * @Route("/{id}/delete", name="delete")
     */
    public function deleteAction(Request $request, Scheme $scheme)
    {
        $form = $this->createDeleteForm($scheme);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($scheme);
            $em->flush();

            return $this->redirectToRoute('admin_person_index');
        }

        return $this->render('admin/document/scheme/delete.html.twig', [
            'scheme' => $scheme,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Creates a form to check out all checked in users.
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Scheme $scheme)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_document_scheme_delete', ['id' => $scheme->getId()]))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }

    /**
     * Creates a new default scheme type entity.
     *
     * @Route("/default/new", name="default_new", methods={"GET", "POST"})
     */
    public function newDefaultAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $default = new SchemeDefault();

        $form = $this->createForm('App\Form\Document\Scheme\SchemeDefaultType', $default);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $schemeType = $form->getData()->getSchemeType();
            
            switch($schemeType){
                case 'person':
                    $this->generatePersonScheme($default);
            }


            $em->persist($default);
            foreach ($default->getFields() as $field) {
                $em->persist($field);
            }
            foreach ($default->getExpressions() as $expr) {
                $em->persist($expr);
            }

            $em->flush();

            return $this->redirectToRoute('admin_document_scheme_default_show', ['id' => $default->getId()]);
        }

        return $this->render('admin/document/schemeDefault/new.html.twig', [
            'scheme' => $default,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Finds and displays a default scheme type entity.
     *
     * @Route("/default/{id}", name="default_show", methods={"GET", "POST"})
     */
    public function showDefaultAction(SchemeDefault $default)
    {
        $em = $this->getDoctrine()->getManager();

        $schemes = $em->getRepository(Scheme::class)->findBy(['schemeDefault' => $default->getId()]);
        //fix this at some point, need person repository function that finds person array by doc array.
        $persons = $em->getRepository(Person::class)->findAll();

        $createdAt = $this->events->findOneBy($default, EntityNewEvent::class);
        $modifs = $this->events->findBy($default, EntityUpdateEvent::class);

        return $this->render('admin/document/schemeDefault/show.html.twig', [
            'schemes' => $schemes,
            'createdAt' => $createdAt,
            'modifs' => $modifs,
            'default' => $default,
        ]);
    }

    /**
     * Displays a form to edit an existing default scheme type entity.
     *
     * @Route("/default/{id}/edit", name="default_edit", methods={"GET", "POST"})
     */
    public function editDefaultAction(Request $request, SchemeDefault $default)
    {
        $em = $this->getDoctrine()->getManager();

        $form = $this->createForm('App\Form\Document\Scheme\SchemeDefaultType', $default);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            return $this->redirectToRoute('admin_document_scheme_index');
        }

        return $this->render('admin/document/schemeDefault/edit.html.twig', [
            'scheme' => $default,
            'form' => $form->createView(),
        ]);
    }

    public function generatePersonScheme( SchemeDefault $default) {
        $name1 = new Field();
        $name1->setName("Voornaam");
        $name1->setSlug("voornaam");
        $name1->setUserEditOnly(false);
        $name1->setValueType('string');
        $default->addField($name1);

        $name2 = new Field();
        $name2->setName("Achternaam");
        $name2->setSlug("achternaam");
        $name2->setUserEditOnly(false);
        $name2->setValueType('string');
        $default->addField($name2);

        $canon = new Expression();
        $canon->setName("Canonical");
        $canon->setExpression('voornaam~" "~achternaam');
        $default->addExpression($canon);

        $nameEx = new Expression();
        $nameEx->setName("Naam");
        $nameEx->setExpression('voornaam');
        $default->addExpression($nameEx);

        $nameEx2 = new Expression();
        $nameEx2->setName("Shortname");
        $nameEx2->setExpression('voornaam');
        $default->addExpression($nameEx2);
    }

}
