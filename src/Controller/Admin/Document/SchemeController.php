<?php

namespace App\Controller\Admin\Document;

use App\Entity\Document\Document;
use App\Entity\Person\Person;
use App\Entity\Document\Scheme;
use App\Log\Doctrine\EntityNewEvent;
use App\Log\Doctrine\EntityUpdateEvent;
use App\Log\EventService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

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
     * Creates a new scheme entity.
     *
     * @Route("/new", name="new", methods={"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $scheme = new Scheme();

        $form = $this->createForm('App\Form\Document\SchemeType', $scheme);
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
        $persons = $em->getRepository(Person::class)->findAll();

        $createdAt = $this->events->findOneBy($scheme, EntityNewEvent::class);
        $modifs = $this->events->findBy($scheme, EntityUpdateEvent::class);

        return $this->render('admin/document/scheme/show.html.twig', [
            'persons' => $persons,
            'createdAt' => $createdAt,
            'modifs' => $modifs,
            'scheme' => $scheme,
        ]);
    }

    /**
     * Displays a form to edit an existing person entity.
     *
     * @Route("/{id}/edit", name="edit", methods={"GET", "POST"})
     */
    public function editAction(Request $request, Scheme $scheme)
    {
        $em = $this->getDoctrine()->getManager();

        $form = $this->createForm('App\Form\Document\SchemeType', $scheme);
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
     * Deletes a person entity.
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
}
