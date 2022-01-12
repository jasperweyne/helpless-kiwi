<?php

namespace App\Controller\Admin;

use App\Controller\Helper\RegistrationHelper;
use App\Entity\Activity\Activity;
use App\Entity\Activity\Registration;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Activity controller.
 *
 * @Route("/admin/activity/register", name="admin_activity_registration_")
 */
class RegistrationController extends RegistrationHelper
{
    /**
     * Add someones registration from an activity.
     *
     * @Route("/new/{id}", name="new", methods={"GET", "POST"})
     */
    public function newAction(
        Request $request,
        Activity $activity
    ) {
        $form = $this->createRegistrationNewForm($activity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->storeRegistration($form->getData());

            return $this->handleRedirect($activity->getId());
        } else {
            return $this->render('admin/activity/registration/new.html.twig', [
                'activity' => $activity,
                'form' => $form->createView(),
            ]);
        }
    }

    /**
     * Edit someones registration from an activity.
     *
     * @Route("/edit/{id}", name="edit", methods={"GET", "POST"})
     */
    public function editAction(
        Request $request,
        Registration $registration
    ) {
        $form = $this->createRegistrationEditForm($registration);
        $form->handleRequest($request);
        $em = $this->getDoctrine()->getManager();

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Registratie aangepast!');

            return $this->handleRedirect($registration->getActivity()->getId());
        } else {
            return $this->render('admin/activity/registration/edit.html.twig', [
                'registration' => $registration,
                'form' => $form->createView(),
            ]);
        }
    }

    /**
     * Remove someones registration from an activity.
     *
     * @Route("/delete/{id}", name="delete")
     */
    public function deleteAction(
        Request $request,
        Registration $registration
    ) {
        $url = $this->generateUrl($request->attributes->get('_route'), ['id' => $registration->getId()]);
        $form = $this->createRegistrationDeleteForm($url);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->removeRegistration($registration);

            return $this->handleRedirect($registration->getActivity()->getId());
        } else {
            return $this->render('admin/activity/registration/delete.html.twig', [
                'registration' => $registration,
                'form' => $form->createView(),
            ]);
        }
    }

    /**
     * Add someone in any acitity reserve list.
     *
     * @Route("/reserve/new/{id}", name="reserve_new", methods={"GET", "POST"})
     */
    public function reserveNewAction(
        Request $request,
        Activity $activity
    ) {
        $form = $this->createReserveNewForm($activity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->storeNewReserve($form->getData());

            return $this->handleRedirect($activity->getId());
        } else {
            return $this->render('admin/activity/registration/new.html.twig', [
                'activity' => $activity,
                'form' => $form->createView(),
                'reserve' => true,
            ]);
        }
    }

    /**
     * Promote someone in any acitity reserve list.
     *
     * @Route("/reserve/move/{id}/up", name="reserve_move_up", methods={"GET", "POST"})
     */
    public function reserveMoveUpAction(
        Registration $registration
    ) {
        $returnData = $this->promoteReserve($registration);

        return $this->handleRedirect($returnData);
    }

    /**
     * Demote someone in any acitity reserve list.
     *
     * @Route("/reserve/move/{id}/down", name="reserve_move_down", methods={"GET", "POST"})
     */
    public function reserveMoveDownAction(
        Registration $registration
    ) {
        $returnData = $this->demoteReserve($registration);

        return $this->handleRedirect($returnData);
    }

    private function handleRedirect($id)
    {
        return $this->redirectToRoute('admin_activity_show', [
            'id' => $id,
        ]);
    }
}
