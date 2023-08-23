<?php

namespace App\Controller\Activity;

use App\Calendar\ICalProvider;
use App\Entity\Activity\Activity;
use App\Entity\Activity\PriceOption;
use App\Entity\Activity\Registration;
use App\Entity\Activity\WaitlistSpot;
use App\Entity\Security\LocalAccount;
use App\Event\RegistrationAddedEvent;
use App\Event\RegistrationRemovedEvent;
use App\Template\Attribute\MenuItem;
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
 */
#[Route('/', name: 'activity_')]
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
     */
    #[MenuItem(title: 'Terug naar frontend', menu: 'admin-profile', class: 'mobile')]
    #[MenuItem(title: 'Activiteiten')]
    #[Route('/', name: 'index', methods: ['GET'])]
    public function indexAction(): Response
    {
        $groups = [];
        if (null !== $user = $this->getUser()) {
            assert($user instanceof LocalAccount);
            $groups = $user->getRelations()->toArray();
        }

        $activities = $this->em->getRepository(Activity::class)->findVisibleUpcomingByGroup($groups);

        return $this->render('activity/index.html.twig', [
            'activities' => $activities,
        ]);
    }

    /**
     * Removes all registrations (including waitlist) for the current user.
     */
    #[Route('/activity/{id}/unregister', name: 'unregister', methods: ['POST'])]
    public function unregisterAction(
        Request $request,
        Activity $activity
    ): Response {
        $form = $this->createUnregisterForm($activity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Find waitlist spots for this user
            $waitlist = $this->em->getRepository(WaitlistSpot::class)->findBy([
                'option' => $activity->getOptions()->toArray(),
                'person' => $this->getUser(),
            ]);

            // Remove user from any waitlist for this activity
            foreach ($waitlist as $spot) {
                $this->em->remove($spot);
                $this->addFlash('success', 'Afgemeld van wachtlijst voor optie '.$spot->option->getName());
            }

            // Find registrations for this user
            $registrations = $this->em->getRepository(Registration::class)->findBy([
                'person' => $this->getUser(),
                'option' => $activity->getOptions()->toArray(),
                'deletedate' => null,
            ]);

            // Remove any registrations from user for this activity
            foreach ($registrations as $registration) {
                $this->events->dispatch(new RegistrationRemovedEvent($registration));
            }

            $this->em->flush();

            // Check if any changes happened
            if (0 === count($registrations) + count($waitlist)) {
                $this->addFlash('error', 'Probleem tijdens afmelden');
            }
        }

        return $this->redirectToRoute(
            'activity_show',
            ['id' => $activity->getId()]
        );
    }

    #[Route('/ical', methods: ['GET'])]
    public function callIcal(
        ICalProvider $iCalProvider
    ): Response {
        $publicActivities = $this->em->getRepository(Activity::class)->findVisibleUpcomingByGroup([]); // Only return activities without target audience

        return new Response($iCalProvider->icalFeed($publicActivities));
    }

    /**
     * Creates registration for the current user.
     */
    #[Route('/activity/{id}/register', name: 'register', methods: ['POST'])]
    public function registerAction(
        Request $request,
        Activity $activity
    ): Response {
        $form = $this->createRegisterForm($activity);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var array{single_option: string} $data */
            $data = $form->getData();
            $option = $this->em->getRepository(PriceOption::class)->find($data['single_option']);
            if (null === $option) {
                $this->addFlash('error', 'Probleem met aanmelding.');

                return $this->redirectToRoute(
                    'activity_show',
                    ['id' => $activity->getId()]
                );
            }

            $user = $this->getUser();
            assert($user instanceof LocalAccount);

            // currently only a single registration per person is allowed, this check enforces that
            $registrations = $this->em->getRepository(Registration::class)->count([
                'activity' => $activity,
                'person' => $user,
                'deletedate' => null,
            ]);
            if ($registrations > 0) {
                $this->addFlash('error', 'Je bent al aangemeld voor deze prijsoptie.');

                return $this->redirectToRoute(
                    'activity_show',
                    ['id' => $activity->getId()]
                );
            }

            // Check if the activity is full
            if ($activity->atCapacity()) {
                $waitlist = $this->em->getRepository(WaitlistSpot::class)->count([
                    'option' => $option,
                    'person' => $user,
                ]);

                if ($waitlist > 0) {
                    $this->addFlash('error', 'Je staat al op de wachtlijst voor deze prijsoptie.');
                } else {
                    $this->em->persist(new WaitlistSpot($user, $option));
                    $this->em->flush();

                    $description = match ($activity->getDeadline() > new \DateTime('now')) {
                        true => 'Indien iemand zich afmeld, wordt de eerstvolgende op de wachtlijst automatisch aangemeld. Na de aanmelddeadline ontvang je een melding per e-mail als iemand z\'n ticket aanbiedt.',
                        false => 'Indien iemand z\'n ticket aanbiedt, ontvang je hier een melding van per e-mail.',
                    };

                    $this->addFlash('success', "Je bent aangemeld op de wachtlijst. $description");
                }

                return $this->redirectToRoute(
                    'activity_show',
                    ['id' => $activity->getId()]
                );
            }

            $registration = new Registration();
            $registration
                ->setActivity($activity)
                ->setOption($option)
                ->setPerson($user)
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
     */
    #[Route('/activity/{id}', name: 'show', methods: ['GET'])]
    public function showAction(Activity $activity): Response
    {
        // Find all groups that this user is in
        $groups = [];
        if (null !== $user = $this->getUser()) {
            assert($user instanceof LocalAccount);
            $groups = $user->getRelations()->toArray();
        }

        // Find all price options (with form) for the groups that this user is in
        $options = array_map(fn (PriceOption $option) => [
            'data' => $option,
            'form' => $this->singleRegistrationForm($option, $activity->atCapacity())->createView(),
        ], $this->em->getRepository(PriceOption::class)->findUpcomingByGroup($activity, $groups));

        // Build deregistration form (if applicable)
        $unregister = null;
        if (null !== $this->getUser()) {
            $registration = $this->em->getRepository(Registration::class)->findBy([
                'activity' => $activity,
                'person' => $this->getUser(),
                'deletedate' => null,
            ]);
            $waitlistSpot = $this->em->getRepository(WaitlistSpot::class)->findBy([
                'option' => $activity->getOptions()->toArray(),
                'person' => $this->getUser(),
            ]);

            if (0 !== count($registration) + count($waitlistSpot)) {
                $unregister = $this->createUnregisterForm($activity)->createView();
            }
        }

        return $this->render('activity/show.html.twig', [
            'activity' => $activity,
            'options' => $options,
            'unregister' => $unregister,
        ]);
    }

    public function singleRegistrationForm(PriceOption $option, bool $waitlist): FormInterface
    {
        $activity = $option->getActivity();
        assert(null !== $activity);
        $form = $this->createRegisterForm($activity, $waitlist);
        $form->get('single_option')->setData($option->getId());

        return $form;
    }

    private function createUnregisterForm(Activity $activity): FormInterface
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('activity_unregister', ['id' => $activity->getId()]))
            ->add('submit', SubmitType::class, [
                'attr' => ['class' => 'button delete'],
                'label' => 'Afmelden',
            ])
            ->getForm()
        ;
    }

    private function createRegisterForm(Activity $activity, bool $waitlist = false): FormInterface
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('activity_register', ['id' => $activity->getId()]))
            ->add('single_option', HiddenType::class)
            ->add('submit', SubmitType::class, [
                'attr' => ['class' => 'button '.($waitlist ? 'warning' : 'confirm')],
                'label' => 'Aanmelden'.($waitlist ? ' wachtlijst' : ''),
            ])
            ->getForm()
        ;
    }
}
