<?php

namespace App\Controller\Admin;

use App\Template\Annotation\MenuItem;
use App\Entity\Activity\Activity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Activity controller.
 *
 * @Route("/admin/activity", name="admin_activity_")
 */
class ActivityController extends AbstractController
{
    /**
     * Lists all activities.
     *
     * @MenuItem(title="Activiteiten", activeCriteria="admin_activity_")
     * @Route("/", name="index", methods={"GET"})
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $activities = $em->getRepository(Activity::class)->findAll();

        return $this->render('admin/activity/index.html.twig', [
            'activities' => $activities,
        ]);
    }


    /**
     * Creates a new activity entity.
     *
     * @Route("/new", name="new", methods={"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $activity = new Activity();

        $form = $this->createForm('App\Form\Activity\ActivityType', $activity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($activity);
            $em->flush();

            return $this->redirectToRoute('admin_activity_show', ['id' => $activity->getId()]);
        }

        return $this->render('admin/activity/new.html.twig', [
            'activity' => $activity,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Finds and displays a activity entity.
     *
     * @Route("/{id}", name="show", methods={"GET"})
     */
    public function showAction(Activity $activity)
    {
        return $this->render('admin/activity/show.html.twig', [
            'activity' => $activity,
        ]);
    }

    /**
     * Displays a form to edit an existing activity entity.
     *
     * @Route("/{id}/edit", name="edit", methods={"GET", "POST"})
     */
    public function editAction(Request $request, Activity $activity)
    {
        $editForm = $this->createForm('App\Form\Activity\ActivityType', $activity);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('admin_activity_show', ['id' => $activity->getId()]);
        }

        return $this->render('admin/activity/edit.html.twig', [
            'activity' => $activity,
            'edit_form' => $editForm->createView(),
        ]);
    }

    /**
     * Deletes a ApiKey entity.
     *
     * @Route("/{id}/delete", name="delete")
     */
    public function deleteAction(Request $request, Activity $activity)
    {
        $form = $this->createDeleteForm($activity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($activity);
            $em->flush();

            return $this->redirectToRoute('admin_activity_index');
        }

        return $this->render('admin/activity/delete.html.twig', [
            'activity' => $activity,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Creates a form to check out all checked in users.
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Activity $activity)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_activity_delete', ['id' => $activity->getId()]))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
