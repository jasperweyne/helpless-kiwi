<?php

namespace App\Controller\Activity;

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
    public function __construct(
        protected EventDispatcherInterface $events,
        protected EntityManagerInterface $em,
    ) {
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

    #[Route('/activity/{id}', name: 'interaction', methods: ['POST'])]
    public function interAction(Request $request, Activity $activity): Response
    {
        $user = $this->getUser();
        if (!$user instanceof LocalAccount) {
            throw new \Exception("Current user isn't a user.");
        }

        $form = $this->createInteractionForm($activity);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            $this->addFlash('error', 'Kapot');

            return $this->showAction($activity);
        }

        $engageId = $form->get('engage_single')->getData();
        $disengageId = $form->get('disengage_single')->getData();

        if (null !== $engageId && null !== $disengageId) {
            $this->addFlash('error', 'KAPOT');
        }

        $optionRepository = $this->em->getRepository(PriceOption::class);
        if (null === $disengageId && null !== $engageId && null !== $engage = $optionRepository->find($engageId)) {
            $this->engage($engage, $user);
        } elseif (null === $engageId && null !== $disengageId && null != $disengage = $optionRepository->find($disengageId)) {
            $this->disengage($disengage, $user);
        } else {
            $this->addFlash('error', 'NEE FUKJOU');
        }

        return $this->showAction($activity);
    }

    private function disengage(
        PriceOption $priceOption,
        LocalAccount $user,
    ): void {
        assert(null !== $priceOption->getActivity());
        if ($priceOption->getActivity()->getStart() < new \DateTime('now')) {
            $this->addFlash('error', 'Activiteit is al begonnen');

            return;
        }

        /** @var Registration[] */
        $registrated = $this->em->getRepository(Registration::class)->findBy([
            'activity' => $priceOption->getActivity(),
            'person' => $user,
            'deletedate' => null,
        ], limit: 1);

        /** @var WaitlistSpot[] */
        $waitListSpots = $this->em->getRepository(WaitlistSpot::class)->findBy([
            'person' => $user,
            'option' => $priceOption,
        ], limit: 1);

        if (0 != count($registrated)) {
            $registrated = $registrated[0];
            if ($priceOption->getActivity()->getDeadline() > new \DateTime('now')) {
                $this->events->dispatch(new RegistrationRemovedEvent($registrated));

                return;
            }
            $registrated->setTransferable(new \DateTime('now'));
            $this->em->flush();
        } elseif (0 != count($waitListSpots)) {
            $spot = $waitListSpots[0];
            $this->em->remove($spot);
            $this->em->flush();
        }
    }

    private function engage(
        PriceOption $priceOption,
        LocalAccount $user,
    ): void {
        assert(null !== $priceOption->getActivity());
        if ($priceOption->getActivity()->getStart() < new \DateTime('now')) {
            $this->addFlash('error', 'Activiteit is al begonnen');

            return;
        }

        /** @var Registration[] */
        $registrated = $this->em->getRepository(Registration::class)->findBy([
            'activity' => $priceOption->getActivity(),
            'person' => $user,
            'deletedate' => null,
        ], limit: 1);

        /** @var ?WaitlistSpot $waitlist */
        $waitlist = $this->em->getRepository(WaitlistSpot::class)->findOneBy([
            'option' => $priceOption->getActivity()->getOptions()->toArray(),
            'person' => $user,
        ]);

        if (0 != count($registrated)) {
            if (null == $waitlist) {
                $this->addFlash('error', 'We shat the bed, sorry');

                return;
            }
            $registrated = $registrated[0];
            $registrated->setTransferable(null);
            $this->em->flush();

            return;
        }
        if ($priceOption->getActivity()->getDeadline() > new \DateTime('now')) {
            if ($priceOption->getActivity()->atCapacity()) {
                if (null === $waitlist) {
                    $waitSpot = new WaitlistSpot($user, $priceOption);
                    $this->em->persist($waitSpot);
                    $this->em->flush();
                }

                return;
            }
            $registration = new Registration();
            $registration
                ->setActivity($priceOption->getActivity())
                ->setOption($priceOption)
                ->setPerson($user);

            $this->events->dispatch(new RegistrationAddedEvent($registration));

            return;
        }

        /** @var ?Registration $freeSpot */
        $freeSpot = $this->em->getRepository(Registration::class)->findAvailableTickets($priceOption->getActivity())[0] ?? null;
        if (null === $freeSpot) {
            if (null === $waitlist) {
                $waitSpot = new WaitlistSpot($user, $priceOption);
                $this->em->persist($waitSpot);
                $this->em->flush();
            }

            return;
        }
        $this->events->dispatch(new RegistrationRemovedEvent($freeSpot));
        $registration = new Registration();
        $registration
            ->setActivity($priceOption->getActivity())
            ->setOption($priceOption)
            ->setPerson($user);

        $this->events->dispatch(new RegistrationAddedEvent($registration));
    }

    /**
     * Finds and displays a activity entity.
     */
    #[Route('/activity/{activity}', name: 'show', methods: ['GET'])]
    public function showAction(Activity $activity): Response
    {
        $optionData = null;
        $registration = null;
        if (null !== $user = $this->getUser()) {
            assert($user instanceof LocalAccount);

            // Find all price options for the groups that this user is in
            $groups = $user->getRelations()->toArray();
            $options = $this->em->getRepository(PriceOption::class)->findUpcomingByGroup($activity, $groups);

            // Find current waitlist/registration for user
            $registration = $this->em->getRepository(Registration::class)->findOneBy([
                'activity' => $activity,
                'person' => $user,
                'deletedate' => null,
            ]);

            $waitlist = $this->em->getRepository(WaitlistSpot::class)->findBy([
                'option' => $activity->getOptions()->toArray(),
                'person' => $user,
            ]);

            $optionData = array_combine(
                array_map(fn (PriceOption $option) => strval($option->getId()), $options),
                array_map(fn (PriceOption $option) => [
                    'data' => $option,
                    'engage' => $this->engageForm($option)->createView(),
                    'disengage' => $this->disengageForm($option)->createView(),
                    'waitlist' => 0 < count(array_filter(
                        $waitlist,
                        fn (WaitlistSpot $w) => $w->option === $option
                    )),
                ], $options)
            );
        }

        return $this->render('activity/show.html.twig', [
            'activity' => $activity,
            'options' => $optionData,
            'registration' => $registration,
        ]);
    }

    public function createInteractionForm(Activity $activity): FormInterface
    {
        return $this->createFormBuilder()
            ->setAction(
                $this->generateUrl('activity_interaction', [
                    'id' => $activity->getId(),
                ])
            )
            ->add('engage_single', HiddenType::class)
            ->add('disengage_single', HiddenType::class)
            ->getForm();
    }

    public function engageForm(PriceOption $priceOption): FormInterface
    {
        $activity = $priceOption->getActivity();
        assert(null !== $activity);

        $form = $this->createInteractionForm($activity);
        $form->get('engage_single')->setData($priceOption->getId());

        return $form;
    }

    public function disengageForm(PriceOption $priceOption): FormInterface
    {
        $activity = $priceOption->getActivity();
        assert(null !== $activity);

        $form = $this->createInteractionForm($activity);
        $form->get('disengage_single')->setData($priceOption->getId());

        return $form;
    }
}
