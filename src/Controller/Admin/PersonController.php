<?php

namespace App\Controller\Admin;

use App\Entity\Person\Person;
use App\Log\EventService;
use App\Log\Doctrine\EntityNewEvent;
use App\Log\Doctrine\EntityUpdateEvent;
use App\Template\Annotation\MenuItem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Person controller.
 *
 * @Route("/admin/person", name="admin_person_")
 */
class PersonController extends AbstractController
{
    private $events;

    public function __construct(EventService $events)
    {
        $this->events = $events;
    }

    /**
     * Lists all Contact entities.
     *
     * @MenuItem(title="Personen", menu="admin", activeCriteria="admin_person_")
     * @Route("/", name="index", methods={"GET"})
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $persons = $em->getRepository(Person::class)->findAll();

        return $this->render('admin/person/index.html.twig', [
            'persons' => $persons,
        ]);
    }

    /**
     * Creates a new activity entity.
     *
     * @Route("/new", name="new", methods={"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $person = new Person();

        $form = $this->createForm('App\Form\Person\PersonType', $person);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($person);
            $em->flush();

            return $this->redirectToRoute('admin_person_show', ['id' => $person->getId()]);
        }

        return $this->render('admin/person/new.html.twig', [
            'person' => $person,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Finds and displays a person entity.
     *
     * @Route("/{id}", name="show", methods={"GET"})
     */
    public function showAction(Person $person)
    {
        $em = $this->getDoctrine()->getManager();

        $createdAt = $this->events->findOneBy($person, EntityNewEvent::class);
        $modifs = $this->events->findBy($person, EntityUpdateEvent::class);

        return $this->render('admin/person/show.html.twig', [
            'createdAt' => $createdAt,
            'modifs' => $modifs,
            'person' => $person,
        ]);
    }

    /**
     * Displays a form to edit an existing person entity.
     *
     * @Route("/{id}/edit", name="edit", methods={"GET", "POST"})
     */
    public function editAction(Request $request, Person $person)
    {
        $form = $this->createForm('App\Form\Person\PersonType', $person);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('admin_person_show', ['id' => $person->getId()]);
        }

        return $this->render('admin/person/edit.html.twig', [
            'person' => $person,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Deletes a person entity.
     *
     * @Route("/{id}/delete", name="delete")
     */
    public function deleteAction(Request $request, Person $person)
    {
        $form = $this->createDeleteForm($person);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($person);
            $em->flush();

            return $this->redirectToRoute('admin_person_index');
        }

        return $this->render('admin/person/delete.html.twig', [
            'person' => $person,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Creates a form to check out all checked in users.
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Person $person)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_person_delete', ['id' => $person->getId()]))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
