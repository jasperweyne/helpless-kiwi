<?php

namespace App\Controller\Admin;

use App\Controller\Helper\RegistrationHelper;
use App\Entity\Activity\Activity;
use App\Entity\Activity\Registration;
use App\Mail\MailService;
use App\Provider\Person\PersonRegistry;
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
    private $routeOrigin = 'admin';

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
        return $helper->reserveMoveDownAction($request, $registration, $personRegistry, $this->routeOrigin);
    }
}
