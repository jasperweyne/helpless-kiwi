<?php

namespace App\Controller\Admin;

use App\Entity\Activity\Activity;
use App\Entity\Activity\PriceOption;
use App\Entity\Activity\Registration;
use App\Entity\Group\Group;
use App\Entity\Security\LocalAccount;
use App\Log\Doctrine\EntityNewEvent;
use App\Log\Doctrine\EntityUpdateEvent;
use App\Log\EventService;
use App\Repository\ActivityRepository;
use App\Repository\GroupRepository;
use App\Repository\RegistrationRepository;
use App\Template\Attribute\MenuItem;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Activity controller.
 */
#[Route("/admin/activity", name: "admin_activity_")]
class ActivityController extends AbstractController
{
    /**
     * @var EventService
     */
    private $events;

    /**
     * @var ActivityRepository
     */
    private $activitiesRepo;

    /**
     * @var GroupRepository
     */
    private $groupsRepo;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(
        EventService $events,
        GroupRepository $groups,
        ActivityRepository $activities,
        EntityManagerInterface $em
    ) {
        $this->events = $events;
        $this->activitiesRepo = $activities;
        $this->groupsRepo = $groups;
        $this->em = $em;
    }

    /**
     * Lists all activities.
     */
    #[MenuItem(title: "Activiteiten", menu: "admin", activeCriteria: "admin_activity_")]
    #[Route("/", name: "index", methods: ["GET"])]
    public function indexAction(): Response
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            $activities = $this->activitiesRepo->findBy([], ['start' => 'DESC']);
        } else {
            /** @var LocalAccount */
            $user = $this->getUser();
            $groups = $this->groupsRepo->findSubGroupsForPerson($user);
            $activities = $this->activitiesRepo->findAuthor($groups);
        }

        return $this->render('admin/activity/index.html.twig', [
            'activities' => $activities,
        ]);
    }

    /**
     * Lists all activities with a group as author.
     */
    #[Route("/group/{id}", name: "group", methods: ["GET"])]
    public function groupAction(Group $group): Response
    {
        $activities = $this->activitiesRepo->findAuthor($this->groupsRepo->findSubGroupsFor($group));

        return $this->render('admin/activity/index.html.twig', [
            'activities' => $activities,
        ]);
    }

    /**
     * Creates a new activity entity.
     */
    #[Route("/new", name: "new", methods: ["GET", "POST"])]
    public function newAction(Request $request): Response
    {
        $activity = new Activity();

        $form = $this->createForm('App\Form\Activity\ActivityNewType', $activity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $location = $activity->getLocation();
            assert($location !== null);
            $this->em->persist($activity);
            $this->em->persist($location);
            $this->em->flush();

            return $this->redirectToRoute('admin_activity_show', ['id' => $activity->getId()]);
        }

        return $this->render('admin/activity/new.html.twig', [
            'activity' => $activity,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Finds and displays a activity entity.
     */
    #[Route("/{id}", name: "show", methods: ["GET"])]
    public function showAction(Activity $activity): Response
    {
        $this->denyAccessUnlessGranted('in_group', $activity->getAuthor());

        $createdAt = $this->events->findOneBy($activity, EntityNewEvent::class);
        $modifs = $this->events->findBy($activity, EntityUpdateEvent::class);

        /** @var RegistrationRepository */
        $repository = $this->em->getRepository(Registration::class);

        $regs = $repository->findBy(['activity' => $activity, 'deletedate' => null, 'reserve_position' => null]);
        $deregs = $repository->findDeregistrations($activity);
        $reserve = $repository->findReserve($activity);
        $present = $repository->countPresent($activity);

        return $this->render('admin/activity/show.html.twig', [
            'createdAt' => $createdAt,
            'modifs' => $modifs,
            'activity' => $activity,
            'registrations' => $regs,
            'deregistrations' => $deregs,
            'reserve' => $reserve,
            'present' => $present,
        ]);
    }

    /**
     * Displays a form to edit an existing activity entity.
     */
    #[Route("/{id}/edit", name: "edit", methods: ["GET", "POST"])]
    public function editAction(Request $request, Activity $activity): Response
    {
        $this->denyAccessUnlessGranted('in_group', $activity->getAuthor());

        $form = $this->createForm('App\Form\Activity\ActivityEditType', $activity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();

            return $this->redirectToRoute('admin_activity_show', ['id' => $activity->getId()]);
        }

        return $this->render('admin/activity/edit.html.twig', [
            'activity' => $activity,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Displays a form to edit an existing activity entity.
     */
    #[Route("/{id}/image", name: "image", methods: ["GET", "POST"])]
    public function imageAction(Request $request, Activity $activity): Response
    {
        $this->denyAccessUnlessGranted('in_group', $activity->getAuthor());

        $form = $this->createForm('App\Form\Activity\ActivityImageType', $activity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();

            return $this->redirectToRoute('admin_activity_show', ['id' => $activity->getId()]);
        }

        return $this->render('admin/activity/image.html.twig', [
            'activity' => $activity,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Deletes a ApiKey entity.
     */
    #[Route("/{id}/delete", name: "delete")]
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
     * Finds and displays a activity entity.
     */
    #[Route("/price/new/{id}", name: "price_new", methods: ["GET", "POST"])]
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

            return $this->redirectToRoute('admin_activity_show', ['id' => $activity->getId()]);
        }

        return $this->render('admin/activity/price/new.html.twig', [
            'activity' => $activity,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Finds and displays a activity entity.
     */
    #[Route("/price/{id}", name: "price_edit", methods: ["GET", "POST"])]
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
            $regs = $repository->findBy(['activity' => $activity, 'deletedate' => null, 'reserve_position' => null]);
            if (count($regs) > 0 && $originalPrice < $price->getPrice()) {
                $this->addFlash('error', 'Prijs kan niet verhoogd worden als er al deelnemers geregistreerd zijn');

                return $this->render('admin/activity/price/edit.html.twig', [
                    'option' => $price,
                    'form' => $form->createView(),
                ]);
            }
            $this->em->flush();

            $activityId = $price->getActivity()?->getId();
            assert($activityId !== null);
            return $this->redirectToRoute('admin_activity_show', ['id' => $activityId]);
        }

        return $this->render('admin/activity/price/edit.html.twig', [
            'option' => $price,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Creates a form to set participent presence.
     */
    #[Route("/{id}/present", name: "present")]
    public function presentEditAction(Request $request, Activity $activity): Response
    {
        $this->denyAccessUnlessGranted('in_group', $activity->getAuthor());

        $form = $this->createForm('App\Form\Activity\ActivityEditPresent', $activity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();
            $this->addFlash('success', 'Aanwezigheid aangepast');
        }

        return $this->render('admin/activity/present.html.twig', [
            'activity' => $activity,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Creates a form to set amount participent present.
     */
    #[Route("/{id}/setamountpresent", name: "amount_present", methods: ["GET", "POST"])]
    public function setAmountPresent(Request $request, Activity $activity): Response
    {
        $this->denyAccessUnlessGranted('in_group', $activity->getAuthor());

        $form = $this->createForm('App\Form\Activity\ActivitySetPresentAmount', $activity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();
            $this->addFlash('success', 'Aanwezigen genoteerd!');

            return $this->redirectToRoute('admin_activity_show', ['id' => $activity->getId()]);
        }

        return $this->render('admin/activity/amountpresent.html.twig', [
            'activity' => $activity,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Creates a form to reset amount participent present.
     */
    #[Route("/{id}/resetamountpresent", name: "reset_amount_present")]
    public function resetAmountPresent(Request $request, Activity $activity): Response
    {
        $this->denyAccessUnlessGranted('in_group', $activity->getAuthor());

        $form = $this->createForm('App\Form\Activity\ActivityCountPresent', $activity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $activity->setPresent(null);
            $this->em->flush();
            $this->addFlash('success', 'Aanwezigen geteld!');

            return $this->redirectToRoute('admin_activity_show', ['id' => $activity->getId()]);
        }

        return $this->render('admin/activity/presentcount.html.twig', [
            'activity' => $activity,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Creates a form to check out all checked in users.
     *
     * @return \Symfony\Component\Form\FormInterface The form
     */
    private function createDeleteForm(Activity $activity): FormInterface
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_activity_delete', ['id' => $activity->getId()]))
            ->setMethod('DELETE')
            ->getForm();
    }
}
