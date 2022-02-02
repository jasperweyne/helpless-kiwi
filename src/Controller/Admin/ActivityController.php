<?php

namespace App\Controller\Admin;

use App\Entity\Activity\Activity;
use App\Entity\Activity\PriceOption;
use App\Entity\Activity\Registration;
use App\Entity\Security\LocalAccount;
use App\Log\Doctrine\EntityNewEvent;
use App\Log\Doctrine\EntityUpdateEvent;
use App\Log\EventService;
use App\Template\Annotation\MenuItem;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

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

        if ($this->isGranted('ROLE_ADMIN')) {
            $activities = $em->getRepository(Activity::class)->findBy([], ['start' => 'DESC']);
        } else {
            /** @var LocalAccount $user */
            $user = $this->getUser();
            $activities = $em->getRepository(Activity::class)->findAuthor($user->getActiveGroups());
        }

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

        $form = $this->createForm('App\Form\Activity\Admin\ActivityNewType', $activity);
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
        $this->denyAccessUnlessGranted('in_group', $activity->getAuthor());

        $em = $this->getDoctrine()->getManager();

        $createdAt = $this->events->findOneBy($activity, EntityNewEvent::class);
        $modifs = $this->events->findBy($activity, EntityUpdateEvent::class);

        $repository = $em->getRepository(Registration::class);

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
     *
     * @Route("/{id}/edit", name="edit", methods={"GET", "POST"})
     */
    public function editAction(Request $request, Activity $activity)
    {
        $this->denyAccessUnlessGranted('in_group', $activity->getAuthor());

        $form = $this->createForm('App\Form\Activity\Admin\ActivityEditType', $activity);
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
     * Displays a form to edit an existing activity entity.
     *
     * @Route("/{id}/image", name="image", methods={"GET", "POST"})
     */
    public function imageAction(Request $request, Activity $activity)
    {
        $this->denyAccessUnlessGranted('in_group', $activity->getAuthor());

        $form = $this->createForm('App\Form\Activity\ActivityImageType', $activity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('admin_activity_show', ['id' => $activity->getId()]);
        }

        return $this->render('admin/activity/image.html.twig', [
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
        $this->denyAccessUnlessGranted('in_group', $activity->getAuthor());

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
        $this->denyAccessUnlessGranted('in_group', $activity->getAuthor());

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
        $this->denyAccessUnlessGranted('in_group', $activity->getAuthor());

        $activity = $price->getActivity();
        $originalPrice = $price->getPrice();
        $form = $this->createForm('App\Form\Activity\PriceOptionType', $price);
        $form->handleRequest($request);

        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository(Registration::class);

        if ($form->isSubmitted() && $form->isValid()) {
            $regs = $repository->findBy(['activity' => $activity, 'deletedate' => null, 'reserve_position' => null]);
            if (count($regs) > 0 && $originalPrice < $price->getPrice()) {
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
     * Creates a form to set participent presence.
     *
     * @Route("/{id}/present", name="present")
     */
    public function presentEditAction(Request $request, Activity $activity)
    {
        $this->denyAccessUnlessGranted('in_group', $activity->getAuthor());

        $form = $this->createForm('App\Form\Activity\ActivityEditPresent', $activity);
        $form->handleRequest($request);

        $em = $this->getDoctrine()->getManager();

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Aanwezigheid aangepast');
        }

        return $this->render('admin/activity/present.html.twig', [
            'activity' => $activity,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Creates a form to set amount participent present.
     *
     * @Route("/{id}/setamountpresent", name="amount_present", methods={"GET", "POST"})
     */
    public function setAmountPresent(Request $request, Activity $activity)
    {
        $this->denyAccessUnlessGranted('in_group', $activity->getAuthor());

        $em = $this->getDoctrine()->getManager();

        $form = $this->createForm('App\Form\Activity\ActivitySetPresentAmount', $activity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
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
     *
     * @Route("/{id}/resetamountpresent", name="reset_amount_present")
     */
    public function resetAmountPresent(Request $request, Activity $activity)
    {
        $this->denyAccessUnlessGranted('in_group', $activity->getAuthor());

        $em = $this->getDoctrine()->getManager();

        $form = $this->createForm('App\Form\Activity\ActivityCountPresent', $activity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $activity->setPresent(null);
            $em->flush();
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
