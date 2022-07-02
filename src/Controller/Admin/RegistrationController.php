<?php

namespace App\Controller\Admin;

use App\Entity\Activity\Activity;
use App\Entity\Activity\Registration;
use App\Entity\Order;
use App\Event\RegistrationAddedEvent;
use App\Event\RegistrationRemovedEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Activity controller.
 *
 * @Route("/admin/activity/register", name="admin_activity_registration_")
 */
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
     *
     * @Route("/new/{id}", name="new", methods={"GET", "POST"})
     */
    public function newAction(
        Request $request,
        Activity $activity
    ): Response {
        $this->denyAccessUnlessGranted('in_group', $activity->getAuthor());

        $registration = new Registration();
        $registration->setActivity($activity);

        $form = $this->createForm('App\Form\Activity\RegistrationType', $registration, ['allowed_options' => $activity->getOptions()]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->events->dispatch(new RegistrationAddedEvent($registration));

            return $this->redirectToRoute('admin_activity_show', [
                'id' => $activity->getId()
            ]);
        }

        return $this->render('admin/activity/registration/new.html.twig', [
            'activity' => $activity,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Edit someones registration from an activity from admin.
     *
     * @Route("/edit/{id}", name="edit", methods={"GET", "POST"})
     */
    public function editAction(
        Request $request,
        Registration $registration
    ): Response {
        if (null !== $registration->getActivity()) {
            $this->denyAccessUnlessGranted('in_group', $registration->getActivity()->getAuthor());
        } elseif (!$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Admin registration');
        }

        $form = $this->createForm('App\Form\Activity\RegistrationEditType', $registration, [
            'allowed_options' => $registration->getActivity()->getOptions(),
        ]);
        
        //Check if the form is submitted and valid from Admin
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();
            $this->addFlash('success', 'Registratie aangepast!');

            return $this->redirectToRoute('admin_activity_show', [
                'id' => $registration->getActivity()->getId(),
            ]);
        }

        //Render the Admin Page edit registration page with correct form
        return $this->render('admin/activity/registration/edit.html.twig', [
            'registration' => $registration,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Remove someones registration from an activity.
     *
     * @Route("/delete/{id}", name="delete")
     */
    public function deleteAction(
        Request $request,
        Registration $registration
    ): Response {
        if (null !== $registration->getActivity()) {
            $this->denyAccessUnlessGranted('in_group', $registration->getActivity()->getAuthor());
        } elseif (!$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Admin registration');
        }

        $url = $this->generateUrl($request->attributes->get('_route'), ['id' => $registration->getId()]);
        $form = $this->createRegistrationDeleteForm($url);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->events->dispatch(new RegistrationRemovedEvent($registration));

            return $this->redirectToRoute('admin_activity_show', [
                'id' => $registration->getActivity()->getId()
            ]);
        }

        return $this->render('admin/activity/registration/delete.html.twig', [
            'registration' => $registration,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Add someone in any acitity reserve list.
     *
     * @Route("/reserve/new/{id}", name="reserve_new", methods={"GET", "POST"})
     */
    public function reserveNewAction(
        Request $request,
        Activity $activity
    ): Response {
        $this->denyAccessUnlessGranted('in_group', $activity->getAuthor());

        $registration = new Registration();
        $registration
            ->setActivity($activity)
            ->setReservePosition($this->em->getRepository(Registration::class)->findAppendPosition($activity))
        ;

        $form = $this->createForm('App\Form\Activity\RegistrationType', $registration, [
            'allowed_options' => $activity->getOptions(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->events->dispatch(new RegistrationAddedEvent($registration));

            return $this->redirectToRoute('admin_activity_show', [
                'id' => $activity->getId()
            ]);
        } else {
            return $this->render('admin/activity/registration/new.html.twig', [
                'activity' => $activity,
                'form' => $form->createView(),
                'reserve' => true,
            ]);
        }
    }

    /**
     * Promote someone in any acitity reserve list.
     *
     * @Route("/reserve/move/{id}/up", name="reserve_move_up", methods={"GET", "POST"})
     */
    public function reserveMoveUpAction(
        Registration $registration
    ): Response {
        if (null !== $registration->getActivity()) {
            $this->denyAccessUnlessGranted('in_group', $registration->getActivity()->getAuthor());
        } elseif (!$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Admin registration');
        }
        
        $x1 = $this->em->getRepository(Registration::class)->findBefore($registration->getActivity(), $registration->getReservePosition());
        $x2 = $this->em->getRepository(Registration::class)->findBefore($registration->getActivity(), $x1);

        $registration->setReservePosition(Order::avg($x1, $x2));

        $this->em->flush();

        $person = $registration->getPerson();
        $this->addFlash('success', ($person !== null ? $person->getCanonical() : 'Onbekend').' naar boven verplaatst!');

        return $this->redirectToRoute('admin_activity_show', [
            'id' => $registration->getActivity()->getId()
        ]);
    }

    /**
     * Demote someone in any acitity reserve list.
     *
     * @Route("/reserve/move/{id}/down", name="reserve_move_down", methods={"GET", "POST"})
     */
    public function reserveMoveDownAction(
        Registration $registration
    ): Response {
        if (null !== $registration->getActivity()) {
            $this->denyAccessUnlessGranted('in_group', $registration->getActivity()->getAuthor());
        } elseif (!$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Admin registration');
        }

        $x1 = $this->em->getRepository(Registration::class)->findAfter($registration->getActivity(), $registration->getReservePosition());
        $x2 = $this->em->getRepository(Registration::class)->findAfter($registration->getActivity(), $x1);

        $registration->setReservePosition(Order::avg($x1, $x2));

        $this->em->flush();

        $person = $registration->getPerson();
        $this->addFlash('success', ($person ? $person->getCanonical() : 'Onbekend').' naar beneden verplaatst!');

        return $this->redirectToRoute('admin_activity_show', [
            'id' => $registration->getActivity()->getId()
        ]);
    }


    /**
     * Creates a form to check out all checked in users.
     *
     * @return \Symfony\Component\Form\Form The form
     */
    protected function createRegistrationDeleteForm(
        $actionUrl
    ): FormInterface {
        return $this->createFormBuilder()
            ->setAction($actionUrl)
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
