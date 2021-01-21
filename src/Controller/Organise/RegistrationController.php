<?php

namespace App\Controller\Organise;

use App\Controller\Helper\RegistrationHelper;
use App\Entity\Activity\Activity;
use App\Entity\Activity\Registration;
use App\Entity\Group\Group;
use App\Entity\Group\Relation;
use App\Mail\MailService;
use App\Provider\Person\PersonRegistry;
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
    private $routeOrigin = 'organise';

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
        MailService $mailer,
        PersonRegistry $personRegistry,
        RegistrationHelper $helper
    ) {
        $this->blockUnauthorisedUsers($activity->getAuthor());

        return $helper->newAction($request, $activity, $mailer, $personRegistry, $this->routeOrigin);
    }

    /**
     * Deletes a person entity.
     *
     * @Route("/delete/{id}", name="delete")
     */
    public function deleteAction(
        Request $request,
        Registration $registration,
        MailService $mailer,
        PersonRegistry $personRegistry,
        RegistrationHelper $helper
    ) {
        $this->blockUnauthorisedUsers($registration->getActivity()->getAuthor());

        return $helper->deleteAction($request, $registration, $mailer, $personRegistry, $this->routeOrigin);
    }

    /**
     * @Route("/reserve/new/{id}", name="reserve_new", methods={"GET", "POST"})
     */
    public function reserveNewAction(
        Request $request,
        Activity $activity,
        PersonRegistry $personRegistry,
        RegistrationHelper $helper
    ) {
        $this->blockUnauthorisedUsers($activity->getAuthor());

        return $helper->reserveNewAction($request, $activity, $personRegistry, $this->routeOrigin);
    }

    /**
     * @Route("/reserve/move/{id}/up", name="reserve_move_up", methods={"GET", "POST"})
     */
    public function reserveMoveUpAction(
        Request $request,
        Registration $registration,
        PersonRegistry $personRegistry,
        RegistrationHelper $helper
    ) {
        $this->blockUnauthorisedUsers($registration->getActivity()->getAuthor());

        return $helper->reserveMoveUpAction($request, $registration, $personRegistry, $this->routeOrigin);
    }

    /**
     * @Route("/reserve/move/{id}/down", name="reserve_move_down", methods={"GET", "POST"})
     */
    public function reserveMoveDownAction(
        Request $request,
        Registration $registration,
        PersonRegistry $personRegistry,
        RegistrationHelper $helper
    ) {
        $this->blockUnauthorisedUsers($registration->getActivity()->getAuthor());

        return $helper->reserveMoveDownAction($request, $registration, $personRegistry, $this->routeOrigin);
    }
}
