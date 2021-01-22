<?php

namespace App\Controller\Admin;

use App\Controller\Helper\RegistrationHelper;
use App\Entity\Activity\Activity;
use App\Entity\Activity\Registration;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Activity controller.
 *
 * @Route("/admin/activity/register", name="admin_activity_registration_")
 */
class RegistrationController extends AbstractController
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
        $returnData = $helper->newAction($request, $activity);
        if (!is_null($returnData)) {
            return $this->render('admin/activity/registration/new.html.twig', $returnData);
        }

        return $this->handleRedirect($activity->getId());
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
        $returnData = $helper->deleteAction($request, $registration);
        if (!is_null($returnData)) {
            return $this->render('admin/activity/registration/delete.html.twig', $returnData);
        }

        return $this->handleRedirect($registration->getActivity()->getId());
    }

    /**
     * @Route("/reserve/new/{id}", name="reserve_new", methods={"GET", "POST"})
     */
    public function reserveNewAction(
        Request $request,
        Activity $activity,
        RegistrationHelper $helper
    ) {
        $returnData = $helper->reserveNewAction($request, $activity);
        if (!is_null($returnData)) {
            return $this->render('admin/activity/registration/new.html.twig', $returnData);
        }

        return $this->handleRedirect($activity->getId());
    }

    /**
     * @Route("/reserve/move/{id}/up", name="reserve_move_up", methods={"GET", "POST"})
     */
    public function reserveMoveUpAction(
        Registration $registration,
        RegistrationHelper $helper
    ) {
        $helper->reserveMoveUpAction($registration);

        return $this->handleRedirect($registration->getActivity()->getId());
    }

    /**
     * @Route("/reserve/move/{id}/down", name="reserve_move_down", methods={"GET", "POST"})
     */
    public function reserveMoveDownAction(
        Registration $registration,
        RegistrationHelper $helper
    ) {
        $helper->reserveMoveDownAction($registration);

        return $this->handleRedirect($registration->getActivity()->getId());
    }

    private function handleRedirect($id)
    {
        return $this->redirectToRoute('admin_activity_show', [
            'id' => $id,
        ]);
    }
}
