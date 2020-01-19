<?php

namespace App\Controller\Admin\Person;

use App\Entity\Person\PersonScheme;
use App\Log\Doctrine\EntityNewEvent;
use App\Log\Doctrine\EntityUpdateEvent;
use App\Log\EventService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Person controller.
 *
 * @Route("/admin/person/scheme", name="admin_person_scheme_")
 */
class PersonSchemeController extends AbstractController
{
    private $events;

    public function __construct(EventService $events)
    {
        $this->events = $events;
    }

    /**
     * Creates a new activity entity.
     *
     * @Route("/new", name="new", methods={"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $scheme = new PersonScheme();

        $form = $this->createForm('App\Form\Person\PersonSchemeType', $scheme);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($scheme);
            $em->flush();

            return $this->redirectToRoute('admin_person_index');
        }

        return $this->render('admin/person/scheme/new.html.twig', [
            'scheme' => $scheme,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Finds and displays a person entity.
     *
     * @Route("/{id}", name="show", methods={"GET", "POST"})
     */
    public function showAction(PersonScheme $scheme)
    {
        $createdAt = $this->events->findOneBy($scheme, EntityNewEvent::class);
        $modifs = $this->events->findBy($scheme, EntityUpdateEvent::class);

        return $this->render('admin/person/scheme/show.html.twig', [
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
    public function editAction(Request $request, PersonScheme $scheme)
    {
        $em = $this->getDoctrine()->getManager();

        $form = $this->createForm('App\Form\Person\PersonSchemeType', $scheme);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            return $this->redirectToRoute('admin_person_index');
        }

        return $this->render('admin/person/scheme/edit.html.twig', [
            'scheme' => $scheme,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Deletes a person entity.
     *
     * @Route("/{id}/delete", name="delete")
     */
    public function deleteAction(Request $request, PersonScheme $scheme)
    {
        $form = $this->createDeleteForm($scheme);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($scheme);
            $em->flush();

            return $this->redirectToRoute('admin_person_index');
        }

        return $this->render('admin/person/scheme/delete.html.twig', [
            'scheme' => $scheme,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Creates a form to check out all checked in users.
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(PersonScheme $scheme)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_person_scheme_delete', ['id' => $scheme->getId()]))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
