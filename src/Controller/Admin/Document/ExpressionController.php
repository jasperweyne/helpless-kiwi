<?php

namespace App\Controller\Admin\Document;

use App\Entity\Document\Expression;
use App\Entity\Document\Scheme;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Person controller.
 *
 * @Route("/admin/document/expression", name="admin_document_expression_")
 */
class ExpressionController extends AbstractController
{
    /**
     * Creates a new activity entity.
     *
     * @Route("/new/{id}", name="new", methods={"GET", "POST"})
     */
    public function newAction(Request $request, Scheme $scheme)
    {
        $em = $this->getDoctrine()->getManager();

        $expression = new Expression();
        $expression->setScheme($scheme);

        $form = $this->createForm('App\Form\Document\ExpressionType', $expression);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($expression);
            $em->flush();

            return $this->redirectToRoute('admin_document_scheme_show', ['id' => $expression->getScheme()->getId()]);
        }

        return $this->render('admin/document/expression/new.html.twig', [
            'expression' => $expression,
            'scheme' => $scheme,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Displays a form to edit an existing person entity.
     *
     * @Route("/{id}/edit", name="edit", methods={"GET", "POST"})
     */
    public function editAction(Request $request, Expression $expression)
    {
        $em = $this->getDoctrine()->getManager();

        $form = $this->createForm('App\Form\Document\ExpressionType', $expression);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            return $this->redirectToRoute('admin_document_scheme_show', ['id' => $expression->getScheme()->getId()]);
        }

        return $this->render('admin/document/expression/edit.html.twig', [
            'expression' => $expression,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Deletes a person entity.
     *
     * @Route("/{id}/delete", name="delete")
     */
    public function deleteAction(Request $request, Expression $expression)
    {
        $form = $this->createDeleteForm($expression);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($expression);
            $em->flush();

            return $this->redirectToRoute('admin_document_scheme_show', ['id' => $expression->getScheme()->getId()]);
        }

        return $this->render('admin/document/expression/delete.html.twig', [
            'expression' => $expression,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Creates a form to check out all checked in users.
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Expression $expression)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_document_expression_delete', ['id' => $expression->getId()]))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
