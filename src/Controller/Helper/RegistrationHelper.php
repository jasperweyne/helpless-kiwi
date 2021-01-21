<?php

namespace App\Controller\Helper;

use App\Entity\Activity\Activity;
use App\Entity\Activity\Registration;
use App\Entity\Order;
use App\Mail\MailService;
use App\Provider\Person\PersonRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationHelper extends AbstractController
{
    public function newAction(
        Request $request,
        Activity $activity,
        MailService $mailer,
        PersonRegistry $personRegistry,
        $origin
    ) {
        $registration = new Registration();
        $registration->setActivity($activity);

        $now = new \DateTime('now');
        $registration->setNewDate($now);

        $form = $this->createForm('App\Form\Activity\RegistrationType', $registration, [
            'allowed_options' => $activity->getOptions(),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($registration);

            $this->sendConvermationMail($registration, $mailer, $personRegistry, $em, 'Aanmeldbericht', 'email/newregistration_by');

            return $this->redirectToRoute($origin.'_activity_show', ['id' => $activity->getId()]);
        }

        return $this->render($origin.'/activity/registration/new.html.twig', [
            'activity' => $activity,
            'form' => $form->createView(),
        ]);
    }

    public function deleteAction(
        Request $request,
        Registration $registration,
        MailService $mailer,
        PersonRegistry $personRegistry,
        $origin
    ) {
        $form = $this->createRegistrationDeleteForm($registration);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $now = new \DateTime('now');
            $em = $this->getDoctrine()->getManager();
            $registration->setDeleteDate($now);

            $this->sendConvermationMail($registration, $mailer, $personRegistry, $em, 'Afmeldbericht ', 'email/removedregistration_by');

            return $this->redirectToRoute($origin.'_activity_show', ['id' => $registration->getActivity()->getId()]);
        }

        return $this->render($origin.'/activity/registration/delete.html.twig', [
            'registration' => $registration,
            'form' => $form->createView(),
        ]);
    }

    public function reserveNewAction(
        Request $request,
        Activity $activity,
        PersonRegistry $personRegistry,
        $origin
    ) {
        $em = $this->getDoctrine()->getManager();

        $position = $em->getRepository(Registration::class)->findAppendPosition($activity);

        $registration = new Registration();
        $registration
            ->setActivity($activity)
            ->setReservePosition($position)
        ;

        $now = new \DateTime('now');
        $registration->setNewDate($now);

        $form = $this->createForm('App\Form\Activity\RegistrationType', $registration, [
            'allowed_options' => $activity->getOptions(),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($registration);
            $em->flush();

            $person = $personRegistry->find($registration->getPersonId());
            $this->addFlash('success', ($person ? $person->getCanonical() : 'Onbekend').' aangemeld op de reservelijst!');

            return $this->redirectToRoute($origin.'_activity_show', ['id' => $activity->getId()]);
        }

        return $this->render($origin.'/activity/registration/new.html.twig', [
            'activity' => $activity,
            'form' => $form->createView(),
            'reserve' => true,
        ]);
    }

    public function reserveMoveUpAction(
        Request $request,
        Registration $registration,
        PersonRegistry $personRegistry,
        $origin
    ) {
        $em = $this->getDoctrine()->getManager();

        $x1 = $em->getRepository(Registration::class)->findBefore($registration->getActivity(), $registration->getReservePosition());
        $x2 = $em->getRepository(Registration::class)->findBefore($registration->getActivity(), $x1);

        $registration->setReservePosition(Order::avg($x1, $x2));

        $em->flush();

        $person = $personRegistry->find($registration->getPersonId());
        $this->addFlash('success', ($person ? $person->getCanonical() : 'Onbekend').' naar boven verplaatst!');

        return $this->redirectToRoute($origin.'_activity_show', ['id' => $registration->getActivity()->getId()]);
    }

    /**
     * Displays a form to edit an existing activity entity.
     *
     * @Route("/reserve/move/{id}/down", name="reserve_move_down", methods={"GET", "POST"})
     */
    public function reserveMoveDownAction(
        Request $request,
        Registration $registration,
        PersonRegistry $personRegistry,
        $origin
    ) {
        $em = $this->getDoctrine()->getManager();

        $x1 = $em->getRepository(Registration::class)->findAfter($registration->getActivity(), $registration->getReservePosition());
        $x2 = $em->getRepository(Registration::class)->findAfter($registration->getActivity(), $x1);

        $registration->setReservePosition(Order::avg($x1, $x2));

        $em->flush();

        $person = $personRegistry->find($registration->getPersonId());
        $this->addFlash('success', ($person ? $person->getCanonical() : 'Onbekend').' naar beneden verplaatst!');

        return $this->redirectToRoute($origin.'_activity_show', ['id' => $registration->getActivity()->getId()]);
    }

    /**
     * Creates a form to check out all checked in users.
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createRegistrationDeleteForm(
        Registration $registration
    ) {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_activity_registration_delete', ['id' => $registration->getId()]))
            ->setMethod('DELETE')
            ->getForm()
                ;
    }

    private function sendconvermationmail(
        Registration $registration,
        MailService $mailer,
        PersonRegistry $personRegistry,
        $em,
        $title,
        $template
    ) {
        $em->flush();
        $person = $personRegistry->find($registration->getPersonId());
        $this->addFlash('success', ($person ? $person->getCanonical() : 'Onbekend').' afgemeld!');

        $title = $title.' '.$registration->getActivity()->getName();
        $body = $this->renderView($template.'.html.twig', [
            'person' => $person,
            'activity' => $registration->getActivity(),
            'title' => $title,
            'by' => $this->getUser()->getPerson(),
        ]);

        $mailer->message($person, $title, $body);
    }
}
