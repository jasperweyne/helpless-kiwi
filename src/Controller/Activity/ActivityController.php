<?php

namespace App\Controller\Activity;

use App\Calendar\ICalProvider;
use App\Entity\Activity\Activity;
use App\Entity\Activity\PriceOption;
use App\Entity\Activity\Registration;
use App\Entity\Group\Group;
use App\Entity\Security\LocalAccount;
use App\Event\RegistrationAddedEvent;
use App\Event\RegistrationRemovedEvent;
use App\Template\Annotation\MenuItem;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Activity controller.
 *
 * @Route("/", name="activity_")
 */
class ActivityController extends AbstractController
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
     * Lists all activities.
     *
     * @MenuItem(title="Terug naar frontend", menu="admin-profile", class="mobile")
     * @MenuItem(title="Activiteiten")
     * @Route("/", name="index", methods={"GET"})
     */
    public function indexAction(): Response
    {
        $groups = [];
        if ($user = $this->getUser()) {
            $groups = $this->em->getRepository(Group::class)->findAllFor($user);
        }

        $activities = $this->em->getRepository(Activity::class)->findVisibleUpcomingByGroup($groups);

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
        Activity $activity
    ): Response {
        $form = $this->createUnregisterForm($activity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $registration = $this->em->getRepository(Registration::class)->find($data['registration_single'] ?? '');

            if ($registration !== null) {
                $event = new RegistrationRemovedEvent($registration);
                $this->events->dispatch($event);
            } else {
                $this->addFlash('error', 'Probleem tijdens afmelden');
            }
        }
        return $this->redirectToRoute(
            'activity_show',
            ['id' => $activity->getId()]
        );
    }

    /**
     * @Route("/ical", methods={"GET"})
     */
    public function callIcal(
        ICalProvider $iCalProvider
    ): Response {
        $publicActivities = $this->em->getRepository(Activity::class)->findVisibleUpcomingByGroup([]); // Only return activities without target audience

        return new Response($iCalProvider->IcalFeed($publicActivities));
    }

    /**
     * Displays a form to register to an activity.
     *
     * @Route("/activity/{id}/register", name="register", methods={"POST"})
     */
    public function registerAction(
        Request $request,
        Activity $activity
    ): Response {
        $form = $this->createRegisterForm($activity);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $option = $this->em->getRepository(PriceOption::class)->find($data['single_option'] ?? null);
            if ($option === null) {
                $this->addFlash('error', 'Probleem met aanmelding.');
                return $this->redirectToRoute(
                    'activity_show',
                    ['id' => $activity->getId()]
                );
            }

            // currently only a single registration per person is allowed, this check enforces that
            $registrations = $this->em->getRepository(Registration::class)->count([
                'activity' => $activity,
                'person' => $this->getUser(),
                'deletedate' => null,
            ]);
            if ($registrations > 0) {
                $this->addFlash('error', 'Je bent al aangemeld voor deze prijsoptie.');
                return $this->redirectToRoute(
                    'activity_show',
                    ['id' => $activity->getId()]
                );
            }

            // create reserve registration if the activity is full
            $registrations = $this->em->getRepository(Registration::class)->findBy([
                'activity' => $activity,
                'reserve_position' => null,
                'deletedate' => null,
            ]);
            $reserve = $activity->hasCapacity() && (count($registrations) >= $activity->getCapacity() || count($this->em->getRepository(Registration::class)->findReserve($activity)) > 0);

            $user = $this->getUser();
            assert($user instanceof LocalAccount);

            $registration = new Registration();
            $registration
                ->setActivity($activity)
                ->setOption($option)
                ->setPerson($user)
                ->setReservePosition($reserve ? $this->em->getRepository(Registration::class)->findAppendPosition($activity) : null)
            ;

            $event = new RegistrationAddedEvent($registration);
            $this->events->dispatch($event);
        }

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
    public function showAction(Activity $activity): Response
    {
        $regs = $this->em->getRepository(Registration::class)->findBy([
            'activity' => $activity,
            'deletedate' => null,
            'reserve_position' => null,
        ]);
        $reserve = $this->em->getRepository(Registration::class)->findReserve($activity);
        $hasReserve = $activity->hasCapacity() && (count($regs) >= $activity->getCapacity() || count($reserve) > 0);
        $groups = [];
        if ($user = $this->getUser()) {
            $groups = $this->em->getRepository(Group::class)->findAllFor($user);
        }
        $targetoptions = $this->em->getRepository(PriceOption::class)->findUpcomingByGroup($activity, $groups);
        $forms = [];
        foreach ($targetoptions as $option) {
            $forms[] = [
                'data' => $option,
                'form' => $this->singleRegistrationForm($option, $hasReserve)->createView(),
            ];
        }
        $unregister = null;
        if (null !== $this->getUser()) {
            $registration = $this->em->getRepository(Registration::class)->findOneBy([
                'activity' => $activity,
                'person' => $this->getUser(),
                'deletedate' => null,
            ]);
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

    public function singleUnregistrationForm(Registration $registration): FormInterface
    {
        $form = $this->createUnregisterForm($registration->getActivity());
        $form->get('registration_single')->setData($registration->getId());

        return $form;
    }

    public function singleRegistrationForm(PriceOption $option, bool $reserve): FormInterface
    {
        $form = $this->createRegisterForm($option->getActivity(), $reserve);
        $form->get('single_option')->setData($option->getId());

        return $form;
    }

    private function createUnregisterForm(Activity $activity): FormInterface
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

    private function createRegisterForm(Activity $activity, bool $reserve = false): FormInterface
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
