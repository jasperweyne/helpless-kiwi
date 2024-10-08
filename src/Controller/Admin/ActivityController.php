<?php

namespace App\Controller\Admin;

use App\Entity\Activity\Activity;
use App\Entity\Activity\PriceOption;
use App\Entity\Activity\Registration;
use App\Entity\Group\Group;
use App\Entity\Security\LocalAccount;
use App\Form\Activity\ActivityCreationData;
use App\Form\Activity\ActivityCreationFlow;
use App\Log\Doctrine\EntityNewEvent;
use App\Log\Doctrine\EntityUpdateEvent;
use App\Log\EventService;
use App\Repository\ActivityRepository;
use App\Repository\GroupRepository;
use App\Repository\RegistrationRepository;
use App\Template\Attribute\MenuItem;
use App\Template\Attribute\SubmenuItem;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Activity controller.
 */
#[Route('/admin/activity', name: 'admin_activity_')]
class ActivityController extends AbstractController
{
    public function __construct(
        private EventService $events,
        private GroupRepository $groupsRepo,
        private ActivityRepository $activitiesRepo,
        private EntityManagerInterface $em,
    ) {
    }

    /**
     * Lists all activities.
     */
    #[MenuItem(title: 'Activiteiten', menu: 'admin', activeCriteria: 'admin_activity_', sub: [
        new SubmenuItem(title: 'Archief', path: 'admin_activity_index', param: [
            'cat' => 'archived',
        ]),
    ])]
    #[Route('/{cat}', name: 'index', methods: ['GET'], requirements: [
        'cat' => 'active|archived',
    ])]
    public function indexAction(string $cat = 'active'): Response
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            $activities = match ($cat) {
                'active' => $this->activitiesRepo->findActive(),
                'archived' => $this->activitiesRepo->findArchived(),
                default => throw $this->createNotFoundException(),
            };
        } else {
            /** @var LocalAccount $user */
            $user = $this->getUser();
            $groups = $this->groupsRepo->findSubGroupsForPerson($user);
            $activities = match ($cat) {
                'active' => $this->activitiesRepo->findAuthor($groups),
                'archived' => $this->activitiesRepo->findAuthorArchive($groups),
                default => throw $this->createNotFoundException(),
            };
        }

        $page_title = match ($cat) {
            'active' => 'Activiteiten',
            'archived' => 'Archief',
            default => null,
        };

        return $this->render('admin/activity/index.html.twig', [
            'page_title' => $page_title,
            'activities' => $activities,
        ]);
    }

    /**
     * Lists all activities with a group as author.
     */
    #[Route('/group/{group}', name: 'group', methods: ['GET'])]
    public function groupAction(Group $group): Response
    {
        $activities = $this
            ->activitiesRepo
            ->findAuthor([$group, ...$this->groupsRepo->findSubGroupsFor($group)]);

        return $this->render('admin/activity/index.html.twig', [
            'activities' => $activities,
        ]);
    }

    /**
     * Creates a new activity entity.
     */
    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function newAction(ActivityCreationFlow $flow): Response
    {
        $activity = new Activity();
        $activityData = new ActivityCreationData();
        $activityData->activity = $activity;

        $flow->bind($activityData);
        $form = $flow->createForm();

        if ($flow->isValid($form)) {
            $flow->saveCurrentStepData($form);
            if ($flow->nextStep()) {
                $form = $flow->createForm();
            } else {
                if (null !== $activityData->price) {
                    $option = (new PriceOption())
                        ->setName('standaard')
                        ->setPrice($activityData->price)
                        ->setDetails([])
                        ->setConfirmationMsg('');
                    $this->em->persist($option);
                    $activity->addOption($option);
                }

                if (null !== $newLocation = $activityData->newLocation) {
                    $this->em->persist($newLocation);
                    $activity->setLocation($newLocation);
                }

                $this->em->persist($activity);
                $this->em->flush();
                $this->addFlash('success', 'Activiteit succesvol aangemaakt');
                $flow->reset();

                return $this->redirectToRoute(
                    'admin_activity_show',
                    ['activity' => $activity->getId()]
                );
            }
        }

        return $this->render('admin/activity/new.html.twig', [
            'form' => $form->createView(),
            'flow' => $flow,
        ]);
    }

    /**
     * Finds and displays a activity entity.
     */
    #[Route('/{activity}', name: 'show', methods: ['GET'])]
    public function showAction(Activity $activity): Response
    {
        $this->denyAccessUnlessGranted('in_group', $activity->getAuthor());

        $createdAt = $this->events->findOneBy($activity, EntityNewEvent::class);
        $modifs = $this->events->findBy($activity, EntityUpdateEvent::class);

        /** @var RegistrationRepository */
        $repository = $this->em->getRepository(Registration::class);
        $deregs = $repository->findDeregistrations($activity);

        return $this->render('admin/activity/show.html.twig', [
            'createdAt' => $createdAt,
            'modifs' => $modifs,
            'activity' => $activity,
            'deregistrations' => $deregs,
        ]);
    }

    /**
     * Displays a form to edit an existing activity entity.
     */
    #[Route('/{activity}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function editAction(Request $request, Activity $activity): Response
    {
        $this->denyAccessUnlessGranted('in_group', $activity->getAuthor());

        $form = $this->createForm('App\Form\Activity\ActivityEditType', $activity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();

            return $this->redirectToRoute(
                'admin_activity_show',
                ['activity' => $activity->getId()]
            );
        }

        return $this->render('admin/activity/edit.html.twig', [
            'activity' => $activity,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Clones an existing a activity into a new activity entity.
     */
    #[Route('/{base}/clone', name: 'clone', methods: ['GET', 'POST'])]
    public function cloneAction(ActivityCreationFlow $flow, Activity $base): Response
    {
        $activity = clone $base;
        $activityData = new ActivityCreationData();
        $activityData->activity = $activity;

        $flow->bind($activityData);
        $form = $flow->createForm();

        if ($flow->isValid($form)) {
            $flow->saveCurrentStepData($form);
            if ($flow->nextStep()) {
                $form = $flow->createForm();
            } else {
                if (null !== $activityData->price) {
                    $option = (new PriceOption())
                        ->setName('standaard')
                        ->setPrice($activityData->price)
                        ->setDetails([])
                        ->setConfirmationMsg('');
                    $this->em->persist($option);
                    $activity->addOption($option);
                }

                if (null !== $newLocation = $activityData->newLocation) {
                    $this->em->persist($newLocation);
                    $activity->setLocation($newLocation);
                }
                foreach ($activity->getOptions() as $priceOption) {
                    $this->em->persist($priceOption);
                }

                $this->em->persist($activity);
                $this->em->flush();
                $this->addFlash('success', 'Activiteit succesvol aangemaakt');
                $flow->reset();
            }

            return $this->redirectToRoute(
                'admin_activity_show',
                ['activity' => $activity->getId()]
            );
        }

        return $this->render('admin/activity/new.html.twig', [
            'form' => $form->createView(),
            'flow' => $flow,
            'page_title' => 'Activiteit kopiëren',
        ]);
    }

    /**
     * Displays a form to edit an existing activity entity.
     */
    #[Route('/{activity}/image', name: 'image', methods: ['GET', 'POST'])]
    public function imageAction(Request $request, Activity $activity): Response
    {
        $this->denyAccessUnlessGranted('in_group', $activity->getAuthor());

        $form = $this->createForm('App\Form\Activity\ActivityImageType', $activity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();

            return $this->redirectToRoute(
                'admin_activity_show',
                ['activity' => $activity->getId()]
            );
        }

        return $this->render('admin/activity/image.html.twig', [
            'activity' => $activity,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Deletes an activity.
     */
    #[Route('/{activity}/delete', name: 'delete')]
    public function deleteAction(Request $request, Activity $activity): Response
    {
        $this->denyAccessUnlessGranted('in_group', $activity->getAuthor());

        $form = $this->createDeleteForm($activity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->remove($activity);
            $this->em->flush();

            return $this->redirectToRoute('admin_activity_index');
        }

        return $this->render('admin/activity/delete.html.twig', [
            'activity' => $activity,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Archives an activity.
     */
    #[Route('/{activity}/archive', name: 'archive')]
    public function archiveAction(Request $request, Activity $activity): Response
    {
        $this->denyAccessUnlessGranted('in_group', $activity->getAuthor());

        $form = $this->createArchiveForm($activity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $activity->setArchived(true);
            $this->em->persist($activity);
            $this->em->flush();

            return $this->redirectToRoute('admin_activity_index');
        }

        return $this->render('admin/activity/archive.html.twig', [
            'activity' => $activity,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Activates an activity.
     */
    #[Route('/{activity}/activate', name: 'activate')]
    public function activateAction(Request $request, Activity $activity): Response
    {
        $this->denyAccessUnlessGranted('in_group', $activity->getAuthor());

        $form = $this->createActivateForm($activity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $activity->setArchived(false);
            $this->em->persist($activity);
            $this->em->flush();

            return $this->redirectToRoute('admin_activity_index', [
                'cat' => 'archived',
            ]);
        }

        return $this->render('admin/activity/activate.html.twig', [
            'activity' => $activity,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Finds and displays a activity entity.
     */
    #[Route('/price/new/{activity}', name: 'price_new', methods: ['GET', 'POST'])]
    public function priceNewAction(Request $request, Activity $activity): Response
    {
        $this->denyAccessUnlessGranted('in_group', $activity->getAuthor());

        $price = new PriceOption();
        $price->setActivity($activity);

        $form = $this->createForm('App\Form\Activity\PriceOptionType', $price);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $price
                ->setDetails([])
                ->setConfirmationMsg('');

            $this->em->persist($price);
            $this->em->flush();

            return $this->redirectToRoute(
                'admin_activity_show',
                ['activity' => $activity->getId()]
            );
        }

        return $this->render('admin/activity/price/new.html.twig', [
            'activity' => $activity,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Finds and displays a activity entity.
     */
    #[Route('/price/{price}', name: 'price_edit', methods: ['GET', 'POST'])]
    public function priceEditAction(Request $request, PriceOption $price): Response
    {
        if (null !== $price->getActivity()) {
            $this->denyAccessUnlessGranted('in_group', $price->getActivity()->getAuthor());
        } elseif (!$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Admin price option');
        }

        $activity = $price->getActivity();
        $originalPrice = $price->getPrice();
        $form = $this->createForm('App\Form\Activity\PriceOptionType', $price);
        $form->handleRequest($request);

        $repository = $this->em->getRepository(Registration::class);

        if ($form->isSubmitted() && $form->isValid()) {
            $regs = $repository->findBy([
                'activity' => $activity,
                'deletedate' => null,
                'reserve_position' => null,
            ]);

            if (count($regs) > 0 && $originalPrice < $price->getPrice()) {
                $this->addFlash('error', 'Prijs kan niet verhoogd worden als er al deelnemers geregistreerd zijn');

                return $this->render('admin/activity/price/edit.html.twig', [
                    'option' => $price,
                    'form' => $form->createView(),
                ]);
            }
            $this->em->flush();

            $activityId = $price->getActivity()?->getId();
            assert(null !== $activityId);

            return $this->redirectToRoute('admin_activity_show', ['activity' => $activityId]);
        }

        return $this->render('admin/activity/price/edit.html.twig', [
            'option' => $price,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Creates a form to set participent presence.
     */
    #[Route('/{activity}/present/edit', name: 'present_edit')]
    public function presentEditAction(Request $request, Activity $activity): Response
    {
        $this->denyAccessUnlessGranted('in_group', $activity->getAuthor());

        $form = $this->createForm('App\Form\Activity\ActivityEditPresent', $activity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();
            $this->addFlash('success', 'Aanwezigheid aangepast');
        }

        return $this->render('admin/activity/present/edit.html.twig', [
            'activity' => $activity,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Creates a form to set amount participent present.
     */
    #[Route('/{activity}/present/set', name: 'present_set', methods: ['GET', 'POST'])]
    public function presentSetAction(Request $request, Activity $activity): Response
    {
        $this->denyAccessUnlessGranted('in_group', $activity->getAuthor());

        $form = $this->createForm('App\Form\Activity\ActivitySetPresentAmount', $activity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();
            $this->addFlash('success', 'Aanwezigen genoteerd!');

            return $this->redirectToRoute(
                'admin_activity_show',
                ['activity' => $activity->getId()]
            );
        }

        return $this->render('admin/activity/present/set.html.twig', [
            'activity' => $activity,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Creates a form to reset amount participent present.
     */
    #[Route('/{activity}/present/reset', name: 'present_reset')]
    public function presentResetAction(Request $request, Activity $activity): Response
    {
        $this->denyAccessUnlessGranted('in_group', $activity->getAuthor());

        $form = $this->createForm('App\Form\Activity\ActivityCountPresent', $activity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $activity->setPresent(null);
            $this->em->flush();
            $this->addFlash('success', 'Aanwezigen geteld!');

            return $this->redirectToRoute(
                'admin_activity_show',
                ['activity' => $activity->getId()]
            );
        }

        return $this->render('admin/activity/present/reset.html.twig', [
            'activity' => $activity,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Creates a form to check out all checked in users.
     */
    private function createDeleteForm(Activity $activity): FormInterface
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_activity_delete', [
                'activity' => $activity->getId(),
            ]))
            ->setMethod('DELETE')
            ->getForm();
    }

    private function createArchiveForm(Activity $activity): FormInterface
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_activity_archive', [
                'activity' => $activity->getId(),
            ]))
            ->setMethod('UPDATE')
            ->getForm();
    }

    private function createActivateForm(Activity $activity): FormInterface
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_activity_activate', [
                'activity' => $activity->getId(),
            ]))
            ->setMethod('UPDATE')
            ->getForm();
    }
}
