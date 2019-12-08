<?php

namespace App\Controller\Admin\Person;

use App\Entity\Person\PersonField;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Person controller.
 *
 * @Route("/admin/person/field", name="admin_person_field_")
 */
class PersonFieldController extends AbstractController
{
    /**
     * Creates a new activity entity.
     *
     * @Route("/new", name="new", methods={"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $field = new PersonField();

        $form = $this->createForm('App\Form\Person\PersonFieldType', $field);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($field);
            $em->flush();

            return $this->redirectToRoute('admin_person_index');
        }

        return $this->render('admin/person/field/new.html.twig', [
            'field' => $field,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Displays a form to edit an existing person entity.
     *
     * @Route("/{id}/edit", name="edit", methods={"GET", "POST"})
     */
    public function editAction(Request $request, PersonField $field)
    {
        $em = $this->getDoctrine()->getManager();

        $form = $this->createForm('App\Form\Person\PersonFieldType', $field);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            return $this->redirectToRoute('admin_person_index');
        }

        return $this->render('admin/person/field/edit.html.twig', [
            'field' => $field,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Deletes a person entity.
     *
     * @Route("/{id}/delete", name="delete")
     */
    public function deleteAction(Request $request, PersonField $field)
    {
        $form = $this->createDeleteForm($field);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($field);
            $em->flush();

            return $this->redirectToRoute('admin_person_index');
        }

        return $this->render('admin/person/field/delete.html.twig', [
            'field' => $field,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Creates a form to check out all checked in users.
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(PersonField $field)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_person_field_delete', ['id' => $field->getId()]))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
