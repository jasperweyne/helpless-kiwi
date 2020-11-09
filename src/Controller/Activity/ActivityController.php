<?php

namespace App\Controller\Activity;

use Swift_Attachment;
use App\Mail\MailService;
use App\Entity\Group\Group;
use App\Entity\Activity\Activity;
use App\Entity\Activity\PriceOption;
use App\Entity\Activity\Registration;
use App\Calendar\ICalProvider;
use App\Template\Annotation\MenuItem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Activity controller.
 *
 * @Route("/", name="activity_")
 */
class ActivityController extends AbstractController
{
    /**
     * Lists all activities.
     *
     * @MenuItem(title="Terug naar frontend", menu="admin-profile", class="mobile")
     * @MenuItem(title="Activiteiten")
     * @Route("/", name="index", methods={"GET"})
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $groups = [];
        if ($user = $this->getUser()) {
            $groups = $em->getRepository(Group::class)
                         ->findAllFor($user->getPerson());
        }

        $activities = $em->getRepository(Activity::class)
                         ->findUpcomingByGroup($groups);

        return $this->render('activity/index.html.twig', [
            'activities' => $activities,
        ]);
    }

    /**
     * Displays a form to edit an existing activity entity.
     *
     * @Route("/activity/{id}/unregister", name="unregister", methods={"POST"})
     */
    public function unregisterAction(
        Request $request,
        Activity $activity,
        MailService $mailer
    )
    {
        $em = $this->getDoctrine()->getManager();

        $form = $this->createUnregisterForm($activity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            if (null !== $data['registration_single']) {
                $registration = $em->getRepository(Registration::class)
                                   ->find($data['registration_single']);
                if (null !== $registration) {
                    $now = new \DateTime('now');
                    $registration->setDeleteDate($now);
                    //$em->remove($registration);
                    $em->flush();
                    $this->addFlash('success', 'Afmelding gelukt!');
                    $title = 'Afmeldbevestiging '.$activity->getName();
                    $body = $this->renderView('email/removedregistration.html.twig', [
                        'person' => $this->getUser()->getPerson(),
                        'activity' => $activity,
                        'title' => $title,
                    ]);
                    $mailer->message($this->getUser()->getPerson(), $title, $body);
                    return $this->redirectToRoute(
                        'activity_show',
                        ['id' => $activity->getId()]
                    );
                }
            }
        }
        $this->addFlash('error', 'Probleem tijdens afmelden');
        return $this->redirectToRoute(
            'activity_show',
            ['id' => $activity->getId()]
        );
    }

    /**
     * Displays a form to register to an activity
     *
     * @Route("/activity/{id}/register", name="register", methods={"POST"})
     */
    public function registerAction(
        Request $request,
        Activity $activity,
        MailService $mailer,
        ICalProvider $iCalProvider
    )
    {
        //4 deep nested?
        //TO-DO refactor this
        $em = $this->getDoctrine()->getManager();
        $form = $this->createRegisterForm($activity);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            if (null !== $data['single_option']) {
                $option = $em->getRepository(PriceOption::class)
                             ->find($data['single_option']);
                if (null !== $option) {
                    $registrations = $em->getRepository(Registration::class)->findBy([
                        'activity' => $activity,
                        'person_id' => $this->getUser()->getPerson()->getId(),
                        'deletedate' => null,
                    ]);
                    if (count($registrations) > 0) {
                        $this->addFlash(
                            'error',
                            'Je bent al aangemeld voor deze prijsoptie.'
                        );
                        return $this->redirectToRoute(
                            'activity_show',
                            ['id' => $activity->getId()]
                        );
                    }
                    $reg = new Registration();
                    $reg->setActivity($activity);
                    $reg->setOption($option);
                    $now = new \DateTime('now');
                    $reg->setNewDate($now);
                    $reg->setPersonId($this->getUser()->getPerson()->getId());
                    $registrations = $em->getRepository(Registration::class)->findBy(
                        ['activity' => $activity,
                        'reserve_position' => null,
                        'deletedate' => null]
                    );
                    $reserve = $activity->hasCapacity() &&
                        (count($registrations) >= $activity->getCapacity() ||
                        count($em->getRepository(Registration::class)->findReserve($activity)) > 0);
                    if ($reserve) {
                        $reg->setReservePosition($em->getRepository(Registration::class)
                            ->findAppendPosition($activity));
                    }
                    $em->persist($reg);
                    $em->flush();
                    if ($reserve) {
                        $this->addFlash('success', 'Aanmelding op reservelijst!');
                        //todo
                    } else {
                        $this->addFlash('success', 'Aanmelding gelukt!');
                        $title = 'Aanmeldbevestiging '.$activity->getName();
                        $ical = $iCalProvider->SingleEventIcal($activity);
                        $ics = new Swift_Attachment(
                            $ical->export(),
                            $activity->getName().'.ics',
                            'text/calendar'
                        );
                        $body = $this->renderView('email/newregistration.html.twig', [
                            'person' => $this->getUser()->getPerson(),
                            'activity' => $activity,
                            'title' => $title,
                        ]);
                        $mailer->message(
                            $this->getUser()->getPerson(),
                            $title,
                            $body,
                            [$ics]
                        );
                    }
                    return $this->redirectToRoute(
                        'activity_show',
                        ['id' => $activity->getId()]
                    );
                }
            }
        }
        $this->addFlash('error', 'Probleem tijdens aanmelden');
        return $this->redirectToRoute(
            'activity_show',
            ['id' => $activity->getId()]
        );
    }

    /**
     * Finds and displays a activity entity.
     *
     * @Route("/activity/{id}", name="show", methods={"GET"})
     */
    public function showAction(Activity $activity)
    {
        $em = $this->getDoctrine()->getManager();
        $regs = $em->getRepository(Registration::class)->findBy(
            ['activity' => $activity,
            'deletedate' => null,
            'reserve_position' => null]
        );
        $reserve = $em->getRepository(Registration::class)->findReserve($activity);
        $hasReserve = $activity->hasCapacity() &&
            (count($regs) >= $activity->getCapacity() || count($reserve) > 0);
        $groups = [];
        if ($user = $this->getUser()) {
            $groups = $em->getRepository(Group::class)
                         ->findAllFor($user->getPerson());
        }
        $targetoptions = $em->getRepository(PriceOption::class)
                            ->findUpcomingByGroup($activity, $groups);
        $forms = [];
        foreach ($targetoptions as $option) {
            $forms[] = [
                'data' => $option,
                'form' => $this->singleRegistrationForm($option, $hasReserve)->createView(),
            ];
        }
        $unregister = null;
        if (null !== $this->getUser()) {
            $registration = $em->getRepository(Registration::class)->findOneBy(
                ['activity' => $activity,
                'person_id' => $this->getUser()->getPerson()->getId(),
                'deletedate' => null]
            );
            if (null !== $registration) {
                $unregister = $this->singleUnregistrationForm($registration)->createView();
            }
        }
        return $this->render('activity/show.html.twig', [
            'activity' => $activity,
            'registrations' => $regs,
            'options' => $forms,
            'unregister' => $unregister,
            'reserve' => $reserve,
        ]);
    }

    public function singleUnregistrationForm(Registration $registration)
    {
        $form = $this->createUnregisterForm($registration->getActivity());
        $form->get('registration_single')->setData($registration->getId());
        return $form;
    }

    public function singleRegistrationForm(PriceOption $option, bool $reserve)
    {
        $form = $this->createRegisterForm($option->getActivity(), $reserve);
        $form->get('single_option')->setData($option->getId());
        return $form;
    }

    private function createUnregisterForm(Activity $activity)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('activity_unregister', ['id' => $activity->getId()]))
            ->add('registration_single', HiddenType::class)
            ->add('submit', SubmitType::class, [
                'attr' => ['class' => 'button delete'],
                'label' => 'Afmelden',
            ])
            ->getForm()
        ;
    }

    private function createRegisterForm(Activity $activity, bool $reserve = false)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('activity_register', ['id' => $activity->getId()]))
            ->add('single_option', HiddenType::class)
            ->add('submit', SubmitType::class, [
                'attr' => ['class' => 'button '.($reserve ? 'warning' : 'confirm')],
                'label' => 'Aanmelden'.($reserve ? ' reserve' : ''),
            ])
            ->getForm()
        ;
    }
}

