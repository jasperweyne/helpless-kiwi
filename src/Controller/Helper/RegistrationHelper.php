<?php

namespace App\Controller\Helper;

use App\Entity\Activity\Activity;
use App\Entity\Activity\Registration;
use App\Entity\Order;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class RegistrationHelper extends AbstractController
{
    private $mailer;
    private $personRegistry;

    public function __construct(
        \App\Mail\MailService $mailer,
        \App\Provider\Person\PersonRegistryInterface $personRegistry
    ) {
        $this->mailer = $mailer;
        $this->personRegistry = $personRegistry;
    }

    /**
     * Creates a form to create a new Registration.
     *
     * @return \Symfony\Component\Form\Form The form
     */
    protected function createRegistrationNewForm(
        Activity $activity
    ) {
        $registration = new Registration();
        $registration->setActivity($activity);

        $now = new \DateTime('now');
        $registration->setNewDate($now);

        return $this->createForm('App\Form\Activity\RegistrationType', $registration, [
            'allowed_options' => $activity->getOptions(),
        ]);
    }

    /**
     * Persist addition of the Registration to the database.
     */
    protected function storeRegistration(
        Registration $registration
    ) {
        $em = $this->getDoctrine()->getManager();
        $em->persist($registration);
        $this->sendConvermationMail($registration, $em, 'Aanmeldbericht', 'email/newregistration_by', 'aangemeld!');
    }

    /**
     * Creates a form to check out all checked in users.
     *
     * @return \Symfony\Component\Form\Form The form
     */
    protected function createRegistrationDeleteForm(
        $actionUrl
    ) {
        return $this->createFormBuilder()
            ->setAction($actionUrl)
            ->setMethod('DELETE')
            ->getForm()
        ;
    }

    /**
     *  Persist removal of the Registration to the database.
     */
    protected function removeRegistration(
        Registration $registration
    ) {
        $now = new \DateTime('now');
        $em = $this->getDoctrine()->getManager();
        $registration->setDeleteDate($now);

        $this->sendConvermationMail($registration, $em, 'Afmeldbericht ', 'email/removedregistration_by', 'afgemeld!');
    }

    /**
     * Creates a form to check out all checked in users.
     *
     * @return \Symfony\Component\Form\Form The form
     */
    protected function createReserveNewForm(
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

        return $this->createForm('App\Form\Activity\RegistrationType', $registration, [
            'allowed_options' => $activity->getOptions(),
        ]);
    }

    /**
     * Persist addition of the Reserve to the database.
     */
    protected function storeNewReserve(
        Registration $registration
    ) {
        $em = $this->getDoctrine()->getManager();
        $em->persist($registration);
        $em->flush();

        $person = $this->personRegistry->find($registration->getPersonId());
        $this->addFlash('success', ($person ? $person->getCanonical() : 'Onbekend').' aangemeld op de reservelijst!');
    }

    protected function promoteReserve(
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

    protected function demoteReserve(
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
