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
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Event\SubmitEvent;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvents;
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
            $registrated->setTransferable(true);
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
            $registrated->setTransferable(false);
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
    #[Route('/activity/{activity}', name: 'show', methods: ['GET', 'POST'])]
    public function showAction(Request $request, Activity $activity): Response
    {
        $user = $this->getUser();
        assert(null === $user || $user instanceof LocalAccount);

        $form = $this->createInteractionForm();
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            if (!$form->isValid() || null === $user) {
                $this->addFlash('error', 'Verzoek kon niet verwerkt worden, probeer het opnieuw');

                return $this->redirectToRoute('activity_show', ['activity' => $activity->getId()]);
            }

            $engage = $form->get('engage_single')->getData();
            $disengage = $form->get('disengage_single')->getData();

            assert(null === $engage || $engage instanceof PriceOption);
            assert(null === $disengage || $disengage instanceof PriceOption);

            if (null !== $engage) {
                $this->engage($engage, $user);
            } else {
                assert(null !== $disengage);
                $this->disengage($disengage, $user);
            }

            return $this->redirectToRoute('activity_show', ['activity' => $activity->getId()]);
        }

        $optionData = null;
        if (null !== $user) {
            // Find all price options for the groups that this user is in
            $groups = $user->getRelations()->toArray();
            $options = $this->em->getRepository(PriceOption::class)->findUpcomingByGroup($activity, $groups);

            // Find current waitlist/registration for user
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
        ]);
    }

    private function createInteractionForm(): FormInterface
    {
        $priceOptionTransformer = new CallbackTransformer(
            fn (?PriceOption $option) => null !== $option ? $option->getId() : '',
            fn (?string $id) => null !== $id ? $this->em->getRepository(PriceOption::class)->find($id) ?? throw new TransformationFailedException('Option could not be found') : null
        );

        $builder = $this->createFormBuilder();

        return $builder
            ->add($builder->create('engage_single', HiddenType::class)->addModelTransformer($priceOptionTransformer))
            ->add($builder->create('disengage_single', HiddenType::class)->addModelTransformer($priceOptionTransformer))
            ->addEventListener(FormEvents::SUBMIT, function (SubmitEvent $event): void {
                $data = $event->getData();
                assert(is_array($data));
                if (is_null($data['engage_single']) === is_null($data['disengage_single'])) {
                    $event->getForm()->addError(new FormError('Either one of the fields should be empty.'));
                }
            })
            ->getForm();
    }

    private function engageForm(PriceOption $priceOption): FormInterface
    {
        $activity = $priceOption->getActivity();
        assert(null !== $activity);

        $form = $this->createInteractionForm();
        $form->get('engage_single')->setData($priceOption);

        return $form;
    }

    private function disengageForm(PriceOption $priceOption): FormInterface
    {
        $activity = $priceOption->getActivity();
        assert(null !== $activity);

        $form = $this->createInteractionForm();
        $form->get('disengage_single')->setData($priceOption);

        return $form;
    }
}
