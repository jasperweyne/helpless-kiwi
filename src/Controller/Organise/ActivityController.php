<?php

namespace App\Controller\Organise;

use App\Entity\Activity\Activity;
use App\Entity\Activity\Registration;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Activity\PriceOption;
use App\Entity\Group\Group;
use App\Mail\MailService;

/**
 * Activity controller.
 *
 * @Route("/organise/activity", name="organise_activity_")
 */
class ActivityController extends AbstractController
{
    private function blockUnauthorisedUsers(Group $group)
    {
        $e = $this->createAccessDeniedException('Not authorised for the correct group.');

        $current = $this->getUser();
        if (is_null($current)) {
            throw $e;
        }

        if (!$group->getRelations()->exists(function ($index, $a) use ($current) {
            return $a->getPerson()->getId() === $current->getPerson()->getId();
        })) {
            throw $e;
        }
    }

    /**
     * Creates a new activity entity.
     *
     * @Route("/new/{id}", name="new", methods={"GET", "POST"})
     */
    public function newAction(Request $request, Group $group)
    {
        $this->blockUnauthorisedUsers($group);

        $activity = new Activity();
        $activity
            ->setAuthor($group)
        ;

        $form = $this->createForm('App\Form\Activity\ActivityNewType', $activity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($activity);
            $em->persist($activity->getLocation());
            $em->flush();

            return $this->redirectToRoute('organise_activity_show', ['id' => $activity->getId()]);
        }

        return $this->render('organise/activity/new.html.twig', [
            'activity' => $activity,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Finds and displays a activity entity.
     *
     * @Route("/{id}", name="show", methods={"GET"})
     */
    public function showAction(Activity $activity)
    {
        $this->blockUnauthorisedUsers($activity->getAuthor());

        $em = $this->getDoctrine()->getManager();

        $deregs = $em->getRepository(Registration::class)->findDeregistrations($activity);

        return $this->render('organise/activity/show.html.twig', [
            'activity' => $activity,
            'deregistrations' => $deregs,
        ]);
    }

    /**
     * Displays a form to edit an existing activity entity.
     *
     * @Route("/{id}/edit", name="edit", methods={"GET", "POST"})
     */
    public function editAction(Request $request, Activity $activity)
    {
        $this->blockUnauthorisedUsers($activity->getAuthor());

        $form = $this->createForm('App\Form\Activity\ActivityEditType', $activity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('organise_activity_show', ['id' => $activity->getId()]);
        }

        return $this->render('organise/activity/edit.html.twig', [
            'activity' => $activity,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Displays a form to edit an existing activity entity.
     *
     * @Route("/{id}/image", name="image", methods={"GET", "POST"})
     */
    public function imageAction(Request $request, Activity $activity)
    {
        $this->blockUnauthorisedUsers($activity->getAuthor());

        $form = $this->createForm('App\Form\Activity\ActivityImageType', $activity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('organise_activity_show', ['id' => $activity->getId()]);
        }

        return $this->render('organise/activity/image.html.twig', [
            'activity' => $activity,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Deletes a ApiKey entity.
     *
     * @Route("/{id}/delete", name="delete")
     */
    public function deleteAction(Request $request, Activity $activity)
    {
        $this->blockUnauthorisedUsers($activity->getAuthor());

        $form = $this->createDeleteForm($activity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($activity);
            $em->flush();

            return $this->redirectToRoute('organise_activity_index');
        }

        return $this->render('organise/activity/delete.html.twig', [
            'activity' => $activity,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Finds and displays a activity entity.
     *
     * @Route("/price/new/{id}", name="price_new", methods={"GET", "POST"})
     */
    public function priceNewAction(Request $request, Activity $activity)
    {
        $this->blockUnauthorisedUsers($activity->getAuthor());

        $price = new PriceOption();
        $price->setActivity($activity);

        $form = $this->createForm('App\Form\Activity\PriceOptionType', $price);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $price
                ->setDetails([])
                ->setConfirmationMsg('')
            ;

            $em = $this->getDoctrine()->getManager();
            $em->persist($price);
            $em->flush();

            return $this->redirectToRoute('organise_activity_show', ['id' => $activity->getId()]);
        }

        return $this->render('organise/activity/price/new.html.twig', [
            'activity' => $activity,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Finds and displays a activity entity.
     *
     * @Route("/price/{id}", name="price_edit", methods={"GET", "POST"})
     */
    public function priceEditAction(Request $request, PriceOption $price)
    {
        $this->blockUnauthorisedUsers($price->getActivity()->getAuthor());

        $originalPrice = $price->getPrice();
        $form = $this->createForm('App\Form\Activity\PriceOptionType', $price);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (count($price->getRegistrations()) > 0 && $originalPrice < $price->getPrice()) {
                $this->addFlash('error', 'Prijs kan niet verhoogd worden als er al deelnemers geregistreerd zijn');

                return $this->render('organise/activity/price/edit.html.twig', [
                    'option' => $price,
                    'form' => $form->createView(),
                ]);
            }
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            return $this->redirectToRoute('organise_activity_show', ['id' => $price->getActivity()->getId()]);
        }

        return $this->render('organise/activity/price/edit.html.twig', [
            'option' => $price,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Displays a form to edit an existing activity entity.
     *
     * @Route("/register/new/{id}", name="registration_new", methods={"GET", "POST"})
     */
    public function registrationNewAction(Request $request, Activity $activity, MailService $mailer)
    {
        $this->blockUnauthorisedUsers($activity->getAuthor());

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

            $this->addFlash('success', $registration->getPerson()->getCanonical().' aangemeld!');

            $title = 'Aanmeldbericht '.$registration->getActivity()->getName();
            $body = $this->renderView('email/newregistration_by.html.twig', [
                'person' => $this->getUser()->getPerson(),
                'activity' => $registration->getActivity(),
                'title' => $title,
                'by' => $this->getUser()->getPerson(),
            ]);

            $mailer->message($this->getUser()->getPerson(), $title, $body);

            return $this->redirectToRoute('organise_activity_show', ['id' => $activity->getId()]);
        }

        return $this->render('organise/activity/registration/new.html.twig', [
            'activity' => $activity,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Deletes a person entity.
     *
     * @Route("/registration/delete/{id}", name="registration_delete")
     */
    public function registrationDeleteAction(Request $request, Registration $registration, MailService $mailer)
    {
        $this->blockUnauthorisedUsers($registration->getActivity()->getAuthor());

        $form = $this->createRegistrationDeleteForm($registration);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $now = new \DateTime('now');
            $registration->setDeleteDate($now);

            $em->flush();

            $this->addFlash('success', $registration->getPerson()->getCanonical().' afgemeld!');

            $title = 'Afmeldbericht '.$registration->getActivity()->getName();
            $body = $this->renderView('email/removedregistration_by.html.twig', [
                'person' => $this->getUser()->getPerson(),
                'activity' => $registration->getActivity(),
                'title' => $title,
                'by' => $this->getUser()->getPerson(),
            ]);

            $mailer->message($this->getUser()->getPerson(), $title, $body);

            return $this->redirectToRoute('organise_activity_show', ['id' => $registration->getActivity()->getId()]);
        }

        return $this->render('organise/activity/registration/delete.html.twig', [
            'registration' => $registration,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Creates a form to check out all checked in users.
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createRegistrationDeleteForm(Registration $registration)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('organise_activity_registration_delete', ['id' => $registration->getId()]))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }

    /**
     * Creates a form to check out all checked in users.
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Activity $activity)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('organise_activity_delete', ['id' => $activity->getId()]))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
