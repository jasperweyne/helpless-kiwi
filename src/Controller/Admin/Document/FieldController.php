<?php

namespace App\Controller\Admin\Document;

use App\Entity\Document\Field\Field;
use App\Entity\Document\Scheme\AbstractScheme;
use App\Entity\Document\Scheme\Scheme;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Person controller.
 *
 * @Route("/admin/document/field", name="admin_document_field_")
 */
class FieldController extends AbstractController
{
    /**
     * Creates a new field entity.
     *
     * @Route("/new/{id}", name="new", methods={"GET", "POST"})
     */
    public function newAction(Request $request, AbstractScheme $scheme)
    {
        $em = $this->getDoctrine()->getManager();

        $field = new Field();
        $field->setScheme($scheme);

        $form = $this->createForm('App\Form\Document\FieldType', $field);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($field);
            $em->flush();

            if ($scheme instanceof Scheme) {
                return $this->redirectToRoute('admin_document_scheme_show', ['id' => $field->getScheme()->getId()]);
            } else {
                return $this->redirectToRoute('admin_document_scheme_default_show', ['id' => $field->getScheme()->getId()]);
            }
        }

        return $this->render('admin/document/field/new.html.twig', [
            'field' => $field,
            'scheme' => $scheme,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Displays a form to edit an existing person entity.
     *
     * @Route("/{id}/edit", name="edit", methods={"GET", "POST"})
     */
    public function editAction(Request $request, Field $field)
    {
        $em = $this->getDoctrine()->getManager();

        $form = $this->createForm('App\Form\Document\FieldType', $field);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            if ($field->getScheme() instanceof Scheme) {
                return $this->redirectToRoute('admin_document_scheme_show', ['id' => $field->getScheme()->getId()]);
            } else {
                return $this->redirectToRoute('admin_document_scheme_default_show', ['id' => $field->getScheme()->getId()]);
            }
        }

        return $this->render('admin/document/field/edit.html.twig', [
            'field' => $field,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Deletes a person entity.
     *
     * @Route("/{id}/delete", name="delete")
     */
    public function deleteAction(Request $request, Field $field)
    {
        $form = $this->createDeleteForm($field);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($field);
            $em->flush();

            if ($field->getScheme() instanceof Scheme) {
                return $this->redirectToRoute('admin_document_scheme_show', ['id' => $field->getScheme()->getId()]);
            } else {
                return $this->redirectToRoute('admin_document_scheme_default_show', ['id' => $field->getScheme()->getId()]);
            }        
        }

        return $this->render('admin/document/field/delete.html.twig', [
            'field' => $field,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Creates a form to check out all checked in users.
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Field $field)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_document_field_delete', ['id' => $field->getId()]))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
