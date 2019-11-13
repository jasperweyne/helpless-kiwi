<?php

namespace App\Controller\Admin;

use App\Template\Annotation\MenuItem;
use App\Entity\Activity\Activity;
use App\Entity\Activity\Registration;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Log\EventService;
use App\Log\Doctrine\EntityNewEvent;
use App\Log\Doctrine\EntityUpdateEvent;
use App\Entity\Activity\PriceOption;
use App\Mail\MailService;

/**
 * Activity controller.
 *
 * @Route("/admin/activity", name="admin_activity_")
 */
class ActivityController extends AbstractController
{
    private $events;

    public function __construct(EventService $events)
    {
        $this->events = $events;
    }

    /**
     * Lists all activities.
     *
     * @MenuItem(title="Activiteiten", menu="admin", activeCriteria="admin_activity_")
     * @Route("/", name="index", methods={"GET"})
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $activities = $em->getRepository(Activity::class)->findBy([], ['start' => 'DESC']);

        return $this->render('admin/activity/index.html.twig', [
            'activities' => $activities,
        ]);
    }

    /**
     * Creates a new activity entity.
     *
     * @Route("/new", name="new", methods={"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $activity = new Activity();

        $form = $this->createForm('App\Form\Activity\ActivityType', $activity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($activity);
            $em->persist($activity->getLocation());
            $em->flush();

            return $this->redirectToRoute('admin_activity_show', ['id' => $activity->getId()]);
        }

        return $this->render('admin/activity/new.html.twig', [
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
        $em = $this->getDoctrine()->getManager();

        $createdAt = $this->events->findOneBy($activity, EntityNewEvent::class);
        $modifs = $this->events->findBy($activity, EntityUpdateEvent::class);

        $regs = $em->getRepository(Registration::class)->findBy(['activity' => $activity, 'deletedate' => null]);

        $deregs = $em->getRepository(Registration::class)->findDeregistrations($activity);

        return $this->render('admin/activity/show.html.twig', [
            'createdAt' => $createdAt,
            'modifs' => $modifs,
            'activity' => $activity,
            'registrations' => $regs,
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
        $form = $this->createForm('App\Form\Activity\ActivityType', $activity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('admin_activity_show', ['id' => $activity->getId()]);
        }

        return $this->render('admin/activity/edit.html.twig', [
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
        $form = $this->createDeleteForm($activity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($activity);
            $em->flush();

            return $this->redirectToRoute('admin_activity_index');
        }

        return $this->render('admin/activity/delete.html.twig', [
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

            return $this->redirectToRoute('admin_activity_show', ['id' => $activity->getId()]);
        }

        return $this->render('admin/activity/price/new.html.twig', [
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
        $originalPrice = $price->getPrice();
        $form = $this->createForm('App\Form\Activity\PriceOptionType', $price);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (count($price->getRegistrations()) > 0 && $originalPrice < $price->getPrice()) {
                $this->addFlash('error', 'Prijs kan niet verhoogd worden als er al deelnemers geregistreerd zijn');

                return $this->render('admin/activity/price/edit.html.twig', [
                    'option' => $price,
                    'form' => $form->createView(),
                ]);
            }
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            return $this->redirectToRoute('admin_activity_show', ['id' => $price->getActivity()->getId()]);
        }

        return $this->render('admin/activity/price/edit.html.twig', [
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

            $this->addFlash('success', $registration->getPerson()->getFullname().' aangemeld!');

            $title = 'Aanmeldbericht '.$registration->getActivity()->getName();
            $body = $this->renderView('email/newregistration_by.html.twig', [
                'person' => $this->getUser()->getPerson(),
                'activity' => $registration->getActivity(),
                'title' => $title,
                'by' => $this->getUser()->getPerson(),
            ]);

            $mailer->message($this->getUser()->getPerson(), $title, $body);

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
     * @Route("/registration/delete/{id}", name="registration_delete")
     */
    public function registrationDeleteAction(Request $request, Registration $registration, MailService $mailer)
    {
        $form = $this->createRegistrationDeleteForm($registration);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $now = new \DateTime('now');
            $registration->setDeleteDate($now);

            $em->flush();

            $this->addFlash('success', $registration->getPerson()->getFullname().' afgemeld!');

            $title = 'Afmeldbericht '.$registration->getActivity()->getName();
            $body = $this->renderView('email/removedregistration_by.html.twig', [
                'person' => $this->getUser()->getPerson(),
                'activity' => $registration->getActivity(),
                'title' => $title,
                'by' => $this->getUser()->getPerson(),
            ]);

            $mailer->message($this->getUser()->getPerson(), $title, $body);

            return $this->redirectToRoute('admin_activity_show', ['id' => $registration->getActivity()->getId()]);
        }

        return $this->render('admin/activity/registration/delete.html.twig', [
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
            ->setAction($this->generateUrl('admin_activity_registration_delete', ['id' => $registration->getId()]))
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
            ->setAction($this->generateUrl('admin_activity_delete', ['id' => $activity->getId()]))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
