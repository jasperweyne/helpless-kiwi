<?php

namespace App\Controller\Organise;

use App\Controller\Helper\RegistrationHelper;
use App\Entity\Activity\Activity;
use App\Entity\Activity\Registration;
use App\Entity\Group\Group;
use App\Entity\Group\Relation;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Activity controller.
 *
 * @Route("/organise/activity/register", name="organise_activity_registration_")
 */
class RegistrationController extends AbstractController
{
    protected function blockUnauthorisedUsers(Group $group)
    {
        $e = $this->createAccessDeniedException('Not authorised for the correct group.');

        $current = $this->getUser();
        if (is_null($current)) {
            throw $e;
        }

        if (!$group->getRelations()->exists(function ($index, Relation $a) use ($current) {
            return $a->getPersonId() === $current->getPerson()->getId();
        })) {
            throw $e;
        }
    }

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
        $this->blockUnauthorisedUsers($activity->getAuthor());
        $returnData = $helper->newAction($request, $activity);
        if (!is_null($returnData)) {
            return $this->render('organise/activity/registration/new.html.twig', $returnData);
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
        $this->blockUnauthorisedUsers($registration->getActivity()->getAuthor());
        $returnData = $helper->deleteAction($request, $registration);
        if (!is_null($returnData)) {
            return $this->render('organise/activity/registration/delete.html.twig', $returnData);
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
        $this->blockUnauthorisedUsers($activity->getAuthor());
        $returnData = $helper->reserveNewAction($request, $activity);
        if (!is_null($returnData)) {
            return $this->render('organise/activity/registration/new.html.twig', $returnData);
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
        $this->blockUnauthorisedUsers($registration->getActivity()->getAuthor());
        $returnData = $helper->reserveMoveUpAction($registration);

        return $this->handleRedirect($returnData);
    }

    /**
     * @Route("/reserve/move/{id}/down", name="reserve_move_down", methods={"GET", "POST"})
     */
    public function reserveMoveDownAction(
        Registration $registration,
        RegistrationHelper $helper
    ) {
        $this->blockUnauthorisedUsers($registration->getActivity()->getAuthor());
        $returnData = $helper->reserveMoveDownAction($registration);

        return $this->handleRedirect($returnData);
    }

    private function handleRedirect($id)
    {
        return $this->redirectToRoute('organise_activity_show', [
            'id' => $id,
        ]);
    }
}
