<?php

namespace App\Controller\Organise;

use App\Controller\Helper\RegistrationHelper;
use App\Entity\Activity\Activity;
use App\Entity\Activity\Registration;
use App\Entity\Group\Group;
use App\Entity\Group\Relation;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Activity controller for the organise endpoint.
 *
 * @Route("/organise/activity/register", name="organise_activity_registration_")
 */
class RegistrationController extends RegistrationHelper
{
    /**
     * Make sure edits can only be made to acitivity's you have created.
     */
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
     * Displays a form to edit an activity you are organizing.
     *
     * @Route("/new/{id}", name="new", methods={"GET", "POST"})
     */
    public function newAction(
        Request $request,
        Activity $activity
    ) {
        $this->blockUnauthorisedUsers($activity->getAuthor());
        $form = $this->createRegistrationNewForm($activity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->storeRegistration($form->getData());

            return $this->handleRedirect($activity->getId());
        } else {
            return $this->render('organise/activity/registration/new.html.twig', [
                'activity' => $activity,
                'form' => $form->createView(),
            ]);
        }
    }

    /**
     * Deletes a person from an entity you have organized.
     *
     * @Route("/delete/{id}", name="delete")
     */
    public function deleteAction(
        Request $request,
        Registration $registration
    ) {
        $this->blockUnauthorisedUsers($registration->getActivity()->getAuthor());
        $url = $this->generateUrl($request->attributes->get('_route'), ['id' => $registration->getId()]);
        $form = $this->createRegistrationDeleteForm($url);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->removeRegistration($registration);

            return $this->handleRedirect($registration->getActivity()->getId());
        } else {
            return $this->render('organise/activity/registration/delete.html.twig', [
                'registration' => $registration,
                'form' => $form->createView(),
            ]);
        }
    }

    /**
     * Add someone to the reserve list of an activity you are organizing.
     *
     * @Route("/reserve/new/{id}", name="reserve_new", methods={"GET", "POST"})
     */
    public function reserveNewAction(
        Request $request,
        Activity $activity
    ) {
        $this->blockUnauthorisedUsers($activity->getAuthor());
        $form = $this->createReserveNewForm($activity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->storeNewReserve($form->getData());

            return $this->handleRedirect($activity->getId());
        } else {
            return $this->render('organise/activity/registration/new.html.twig', [
                'activity' => $activity,
                'form' => $form->createView(),
                'reserve' => true,
            ]);
        }
    }

    /**
     * Promote someone in the reserve list of an activity you are organizing.
     *
     * @Route("/reserve/move/{id}/up", name="reserve_move_up", methods={"GET", "POST"})
     */
    public function reserveMoveUpAction(
        Registration $registration
    ) {
        $this->blockUnauthorisedUsers($registration->getActivity()->getAuthor());
        $returnData = $this->promoteReserve($registration);

        return $this->handleRedirect($returnData);
    }

    /**
     * Demote someone in the reserve list of an activity you are organizing.
     *
     * @Route("/reserve/move/{id}/down", name="reserve_move_down", methods={"GET", "POST"})
     */
    public function reserveMoveDownAction(
        Registration $registration
    ) {
        $this->blockUnauthorisedUsers($registration->getActivity()->getAuthor());
        $returnData = $this->demoteReserve($registration);

        return $this->handleRedirect($returnData);
    }

    private function handleRedirect($id)
    {
        return $this->redirectToRoute('organise_activity_show', [
            'id' => $id,
        ]);
    }
}
