<?php

namespace App\Controller\Helper;

use App\Entity\Activity\Activity;
use App\Entity\Activity\Registration;
use App\Entity\Order;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationHelper extends AbstractController
{
    private $mailer;
    private $personRegistry;

    public function __construct(
        \App\Mail\MailService $mailer,
        \App\Provider\Person\PersonRegistry $personRegistry
    ) {
        $this->mailer = $mailer;
        $this->personRegistry = $personRegistry;
    }

    public function newAction(
        Request $request,
        Activity $activity
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

            $this->sendConvermationMail($registration, $em, 'Aanmeldbericht', 'email/newregistration_by', 'aangemeld!');

            return null;
        }

        return [
            'activity' => $activity,
            'form' => $form->createView(),
        ];
    }

    public function deleteAction(
        Request $request,
        Registration $registration
    ) {
        $form = $this->createRegistrationDeleteForm($request, $registration);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $now = new \DateTime('now');
            $em = $this->getDoctrine()->getManager();
            $registration->setDeleteDate($now);

            $this->sendConvermationMail($registration, $em, 'Afmeldbericht ', 'email/removedregistration_by', 'afgemeld!');

            return null;
        }

        return [
            'registration' => $registration,
            'form' => $form->createView(),
        ];
    }

    public function reserveNewAction(
        Request $request,
        Activity $activity
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

            $person = $this->personRegistry->find($registration->getPersonId());
            $this->addFlash('success', ($person ? $person->getCanonical() : 'Onbekend').' aangemeld op de reservelijst!');

            return null;
        }

        return [
            'activity' => $activity,
            'form' => $form->createView(),
            'reserve' => true,
        ];
    }

    public function reserveMoveUpAction(
        Registration $registration
    ) {
        $em = $this->getDoctrine()->getManager();

        $x1 = $em->getRepository(Registration::class)->findBefore($registration->getActivity(), $registration->getReservePosition());
        $x2 = $em->getRepository(Registration::class)->findBefore($registration->getActivity(), $x1);

        $registration->setReservePosition(Order::avg($x1, $x2));

        $em->flush();

        $person = $this->personRegistry->find($registration->getPersonId());
        $this->addFlash('success', ($person ? $person->getCanonical() : 'Onbekend').' naar boven verplaatst!');

        return $registration->getActivity()->getId();
    }

    /**
     * Displays a form to edit an existing activity entity.
     *
     * @Route("/reserve/move/{id}/down", name="reserve_move_down", methods={"GET", "POST"})
     */
    public function reserveMoveDownAction(
        Registration $registration
    ) {
        $em = $this->getDoctrine()->getManager();

        $x1 = $em->getRepository(Registration::class)->findAfter($registration->getActivity(), $registration->getReservePosition());
        $x2 = $em->getRepository(Registration::class)->findAfter($registration->getActivity(), $x1);

        $registration->setReservePosition(Order::avg($x1, $x2));

        $em->flush();

        $person = $this->personRegistry->find($registration->getPersonId());
        $this->addFlash('success', ($person ? $person->getCanonical() : 'Onbekend').' naar beneden verplaatst!');

        return $registration->getActivity()->getId();
    }

    /**
     * Creates a form to check out all checked in users.
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createRegistrationDeleteForm(
        Request $request,
        Registration $registration
    ) {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl($request->attributes->get('_route'), ['id' => $registration->getId()]))
            ->setMethod('DELETE')
            ->getForm()
                ;
    }

    private function sendConvermationMail(
        Registration $registration,
        $em,
        $title,
        $template,
        $message
    ) {
        $em->flush();
        $person = $this->personRegistry->find($registration->getPersonId());
        $this->addFlash('success', ($person ? $person->getCanonical() : 'Onbekend').' '.$message);

        $title = $title.' '.$registration->getActivity()->getName();
        $body = $this->renderView($template.'.html.twig', [
            'person' => $person,
            'activity' => $registration->getActivity(),
            'title' => $title,
            'by' => $this->getUser()->getPerson(),
        ]);

        $this->mailer->message($person, $title, $body);
    }
}
