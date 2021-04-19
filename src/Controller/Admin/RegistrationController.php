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
     * Displays a form to edit an existing activity entity.
     *
     * @Route("/new/{id}", name="new", methods={"GET", "POST"})
     */
    public function newAction(
        Request $request,
        Activity $activity,
        RegistrationHelper $helper
    ) {
        $form = $helper->createRegistrationNewForm($activity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $helper->storeRegistration($form->getData());

            return $this->handleRedirect($activity->getId());
        } else {
            return $this->render('admin/activity/registration/new.html.twig', [
                'activity' => $activity,
                'form' => $form->createView(),
            ]);
        }
    }

    /**
     * Deletes a person entity.
     *
     * @Route("/delete/{id}", name="delete")
     */
    public function deleteAction(
        Request $request,
        Registration $registration,
        RegistrationHelper $helper
    ) {
        $url = $this->generateUrl($request->attributes->get('_route'), ['id' => $registration->getId()]);
        $form = $helper->createRegistrationDeleteForm($url);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $helper->removeRegistration($registration);

            return $this->handleRedirect($registration->getActivity()->getId());
        } else {
            return $this->render('admin/activity/registration/delete.html.twig', [
                'registration' => $registration,
                'form' => $form->createView(),
            ]);
        }
    }

    /**
     * @Route("/reserve/new/{id}", name="reserve_new", methods={"GET", "POST"})
     */
    public function reserveNewAction(
        Request $request,
        Activity $activity,
        RegistrationHelper $helper
    ) {
        $form = $helper->createReserveNewForm($activity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $helper->storeNewReserve($form->getData());

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
     * @Route("/reserve/move/{id}/up", name="reserve_move_up", methods={"GET", "POST"})
     */
    public function reserveMoveUpAction(
        Registration $registration,
        RegistrationHelper $helper
    ) {
        $returnData = $helper->promoteReserve($registration);

        return $this->handleRedirect($returnData);
    }

    /**
     * @Route("/reserve/move/{id}/down", name="reserve_move_down", methods={"GET", "POST"})
     */
    public function reserveMoveDownAction(
        Registration $registration,
        RegistrationHelper $helper
    ) {
        $returnData = $helper->demoteReserve($registration);

        return $this->handleRedirect($returnData);
    }

    private function handleRedirect($id)
    {
        return $this->redirectToRoute('admin_activity_show', [
            'id' => $id,
        ]);
    }
}
