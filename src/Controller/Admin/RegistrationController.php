<?php

namespace App\Controller\Admin;

use App\Entity\Activity\Activity;
use App\Entity\Activity\PriceOption;
use App\Entity\Activity\Registration;
use App\Entity\Activity\WaitlistSpot;
use App\Entity\Security\LocalAccount;
use App\Event\RegistrationAddedEvent;
use App\Event\RegistrationRemovedEvent;
use App\Form\Activity\WaitlistSpotType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Activity controller.
 */
#[Route('/admin/activity/register', name: 'admin_activity_registration_')]
class RegistrationController extends AbstractController
{
    /**
     * @var EventDispatcherInterface
     */
    protected $events;

    /**
     * @var EntityManagerInterface
     */
    protected $em;

    public function __construct(EventDispatcherInterface $events, EntityManagerInterface $em)
    {
        $this->events = $events;
        $this->em = $em;
    }

    /**
     * Add someones registration from an activity.
     */
    #[Route('/new/{id}/external', name: 'new_external', methods: ['GET', 'POST'])]
    public function newExternalAction(
        Request $request,
        Activity $activity
    ): Response {
        return $this->newAction($request, $activity, true);
    }

    /**
     * Add someones registration from an activity.
     */
    #[Route('/new/{id}', name: 'new', methods: ['GET', 'POST'])]
    public function newAction(
        Request $request,
        Activity $activity,
        bool $external = false
    ): Response {
        $this->denyAccessUnlessGranted('in_group', $activity->getAuthor());

        $registration = new Registration();
        $registration->setActivity($activity);

        $form = $this->createForm('App\Form\Activity\RegistrationType', $registration, [
            'allowed_options' => $activity->getOptions(),
            'external_registrant' => $external,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->events->dispatch(new RegistrationAddedEvent($registration));

            return $this->redirectToRoute('admin_activity_show', [
                'id' => $activity->getId(),
            ]);
        }

        return $this->render('admin/activity/registration/new.html.twig', [
            'activity' => $activity,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Edit someones registration from an activity from admin.
     */
    #[Route('/edit/{id}', name: 'edit', methods: ['GET', 'POST'])]
    public function editAction(
        Request $request,
        Registration $registration
    ): Response {
        if (null !== $registration->getActivity()) {
            $this->denyAccessUnlessGranted('in_group', $registration->getActivity()->getAuthor());
        } elseif (!$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Admin registration');
        }

        $activity = $registration->getActivity();
        assert(null !== $activity);

        $form = $this->createForm('App\Form\Activity\RegistrationEditType', $registration, [
            'allowed_options' => $activity->getOptions(),
        ]);

        // Check if the form is submitted and valid from Admin
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();
            $this->addFlash('success', 'Registratie aangepast!');

            return $this->redirectToRoute('admin_activity_show', [
                'id' => $activity->getId(),
            ]);
        }

        // Render the Admin Page edit registration page with correct form
        return $this->render('admin/activity/registration/edit.html.twig', [
            'registration' => $registration,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Remove someones registration from an activity.
     */
    #[Route('/delete/{id}', name: 'delete')]
    public function deleteAction(
        Request $request,
        Registration $registration
    ): Response {
        if (null !== $registration->getActivity()) {
            $this->denyAccessUnlessGranted('in_group', $registration->getActivity()->getAuthor());
        } elseif (!$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Admin registration');
        }

        $activity = $registration->getActivity();
        assert(null !== $activity);

        $form = $this->createRegistrationDeleteForm($registration);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->events->dispatch(new RegistrationRemovedEvent($registration));

            return $this->redirectToRoute('admin_activity_show', [
                'id' => $activity->getId(),
            ]);
        }

        return $this->render('admin/activity/registration/delete.html.twig', [
            'registration' => $registration,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Add someone to an activity waitlist.
     */
    #[Route('/waitlist/new/{id}', name: 'waitlist_add', methods: ['GET', 'POST'])]
    public function waitlistAddAction(
        Request $request,
        Activity $activity
    ): Response {
        $this->denyAccessUnlessGranted('in_group', $activity->getAuthor());

        $form = $this->createForm(WaitlistSpotType::class, null, [
            'allowed_options' => $activity->getOptions(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var array{person: LocalAccount, option: PriceOption} */
            $data = $form->getData();

            $this->em->persist(new WaitlistSpot($data['person'], $data['option']));
            $this->em->flush();

            $this->addFlash('success', 'Aangemeld op de wachtlijst!');

            return $this->redirectToRoute('admin_activity_show', [
                'id' => $activity->getId(),
            ]);
        } else {
            return $this->render('admin/activity/registration/new.html.twig', [
                'activity' => $activity,
                'form' => $form->createView(),
                'waitlist' => true,
            ]);
        }
    }

    /**
     * Remove someone from the activity waitlist.
     */
    #[Route('/waitlist/remove/{id}', name: 'waitlist_remove', methods: ['GET', 'POST'])]
    public function waitlistRemoveAction(
        Request $request,
        WaitlistSpot $spot
    ): Response {
        if (null !== $spot->option->getActivity()) {
            $this->denyAccessUnlessGranted('in_group', $spot->option->getActivity()->getAuthor());
        } elseif (!$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Admin registration');
        }

        $activity = $spot->option->getActivity();
        assert(null !== $activity);

        $form = $this->createWaitlistDeleteForm($spot);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->remove($spot);
            $this->em->flush();

            $this->addFlash('success', 'Afgemeld van de wachtlijst!');

            return $this->redirectToRoute('admin_activity_show', [
                'id' => $activity->getId(),
            ]);
        }

        return $this->render('admin/activity/registration/delete.html.twig', [
            'registration' => $spot,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Creates a form to check out all checked in users.
     *
     * @return \Symfony\Component\Form\FormInterface The form
     */
    protected function createRegistrationDeleteForm(Registration $registration): FormInterface
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
     * @return \Symfony\Component\Form\FormInterface The form
     */
    protected function createWaitlistDeleteForm(WaitlistSpot $spot): FormInterface
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_activity_registration_waitlist_delete', ['id' => $spot->id]))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
