<?php

namespace App\Controller\Admin;

use App\Entity\Activity\Activity;
use App\Entity\Order;
use App\Entity\Activity\Registration;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Mail\MailService;
use App\Provider\Person\PersonRegistry;

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
    public function newAction(Request $request, Activity $activity, MailService $mailer, PersonRegistry $personRegistry)
    {
        $em = $this->getDoctrine()->getManager();

        $registration = new Registration();
        $registration->setActivity($activity);

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
            $this->addFlash('success', ($person ? $person->getCanonical() : 'Onbekend').' aangemeld!');

            $title = 'Aanmeldbericht '.$registration->getActivity()->getName();
            $body = $this->renderView('email/newregistration_by.html.twig', [
                'person' => $person,
                'activity' => $registration->getActivity(),
                'title' => $title,
                'by' => $this->getUser()->getPerson(),
            ]);

            $mailer->message($person, $title, $body);

            return $this->redirectToRoute('admin_activity_show', ['id' => $activity->getId()]);
        }

        return $this->render('admin/activity/registration/new.html.twig', [
            'activity' => $activity,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Deletes a person entity.
     *
     * @Route("/delete/{id}", name="delete")
     */
    public function deleteAction(Request $request, Registration $registration, MailService $mailer, PersonRegistry $personRegistry)
    {
        $form = $this->createRegistrationDeleteForm($registration);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $now = new \DateTime('now');
            $registration->setDeleteDate($now);

            $em->flush();

            $person = $personRegistry->find($registration->getPersonId());
            $this->addFlash('success', ($person ? $person->getCanonical() : 'Onbekend').' afgemeld!');

            $title = 'Afmeldbericht '.$registration->getActivity()->getName();
            $body = $this->renderView('email/removedregistration_by.html.twig', [
                'person' => $person,
                'activity' => $registration->getActivity(),
                'title' => $title,
                'by' => $this->getUser()->getPerson(),
            ]);

            $mailer->message($person, $title, $body);

            return $this->redirectToRoute('admin_activity_show', ['id' => $registration->getActivity()->getId()]);
        }

        return $this->render('admin/activity/registration/delete.html.twig', [
            'registration' => $registration,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Displays a form to edit an existing activity entity.
     *
     * @Route("/reserve/new/{id}", name="reserve_new", methods={"GET", "POST"})
     */
    public function reserveNewAction(Request $request, Activity $activity, MailService $mailer, PersonRegistry $personRegistry)
    {
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

            return $this->redirectToRoute('admin_activity_show', ['id' => $activity->getId()]);
        }

        return $this->render('admin/activity/registration/new.html.twig', [
            'activity' => $activity,
            'form' => $form->createView(),
            'reserve' => true,
        ]);
    }

    /**
     * Displays a form to edit an existing activity entity.
     *
     * @Route("/reserve/move/{id}/up", name="reserve_move_up", methods={"GET", "POST"})
     */
    public function reserveMoveUpAction(Request $request, Registration $registration, PersonRegistry $personRegistry)
    {
        $em = $this->getDoctrine()->getManager();

        $x1 = $em->getRepository(Registration::class)->findBefore($registration->getActivity(), $registration->getReservePosition());
        $x2 = $em->getRepository(Registration::class)->findBefore($registration->getActivity(), $x1);

        $registration->setReservePosition(Order::avg($x1, $x2));

        $em->flush();

        $person = $personRegistry->find($registration->getPersonId());
        $this->addFlash('success', ($person ? $person->getCanonical() : 'Onbekend').' naar boven verplaatst!');

        return $this->redirectToRoute('admin_activity_show', ['id' => $registration->getActivity()->getId()]);
    }

    /**
     * Displays a form to edit an existing activity entity.
     *
     * @Route("/reserve/move/{id}/down", name="reserve_move_down", methods={"GET", "POST"})
     */
    public function reserveMoveDownAction(Request $request, Registration $registration, PersonRegistry $personRegistry)
    {
        $em = $this->getDoctrine()->getManager();

        $x1 = $em->getRepository(Registration::class)->findAfter($registration->getActivity(), $registration->getReservePosition());
        $x2 = $em->getRepository(Registration::class)->findAfter($registration->getActivity(), $x1);

        $registration->setReservePosition(Order::avg($x1, $x2));

        $em->flush();

        $person = $personRegistry->find($registration->getPersonId());
        $this->addFlash('success', ($person ? $person->getCanonical() : 'Onbekend').' naar beneden verplaatst!');

        return $this->redirectToRoute('admin_activity_show', ['id' => $registration->getActivity()->getId()]);
    }

    /**
     * Creates a form to check out all checked in users.
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createRegistrationDeleteForm(Registration $registration)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_activity_registration_delete', ['id' => $registration->getId()]))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
