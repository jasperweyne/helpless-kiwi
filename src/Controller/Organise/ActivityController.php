<?php

namespace App\Controller\Organise;

use App\Entity\Activity\Activity;
use App\Entity\Activity\Registration;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Activity\PriceOption;
use App\Entity\Group\Group;
use App\Entity\Group\Relation;
use App\Mail\MailService;

/**
 * Activity controller.
 *
 * @Route("/organise/activity", name="organise_activity_")
 */
class ActivityController extends AbstractController
{
    protected function blockUnauthorisedUsers(Group $group)
    {
        $e = $this->createAccessDeniedException('Not authorised for the correct group.');

        $current = $this->getUser();
        if (is_null($current)) {
            throw $e;
        }

        if (!$group->getRelations()->exists(function ($index, Relation $a) use ($current) {
            return $a->getPersonId() === $current->getPerson()->getId();
        })) {
            throw $e;
        }
    }

    /**
     * Creates a new activity entity.
     *
     * @Route("/new/{id}", name="new", methods={"GET", "POST"})
     */
    public function newAction(Request $request, Group $group)
    {
        $this->blockUnauthorisedUsers($group);

        $activity = new Activity();
        $activity
            ->setAuthor($group)
        ;

        $form = $this->createForm('App\Form\Activity\ActivityNewType', $activity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($activity);
            $em->persist($activity->getLocation());
            $em->flush();

            return $this->redirectToRoute('organise_activity_show', ['id' => $activity->getId()]);
        }

        return $this->render('organise/activity/new.html.twig', [
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
        $this->blockUnauthorisedUsers($activity->getAuthor());

        $em = $this->getDoctrine()->getManager();

        $repository = $em->getRepository(Registration::class);

        $regs = $repository->findBy(['activity' => $activity, 'deletedate' => null, 'reserve_position' => null]);
        $deregs = $repository->findDeregistrations($activity);
        $reserve = $repository->findReserve($activity);
        $present = $repository->countPresent($activity);

        return $this->render('organise/activity/show.html.twig', [
            'activity' => $activity,
            'registrations' => $regs,
            'deregistrations' => $deregs,
            'reserve' => $reserve,
            'present' => $present
        ]);
    }

    /**
     * Displays a form to edit an existing activity entity.
     *
     * @Route("/{id}/edit", name="edit", methods={"GET", "POST"})
     */
    public function editAction(Request $request, Activity $activity)
    {
        $this->blockUnauthorisedUsers($activity->getAuthor());

        $form = $this->createForm('App\Form\Activity\ActivityEditType', $activity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('organise_activity_show', ['id' => $activity->getId()]);
        }

        return $this->render('organise/activity/edit.html.twig', [
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
        $this->blockUnauthorisedUsers($activity->getAuthor());

        $form = $this->createForm('App\Form\Activity\ActivityImageType', $activity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('organise_activity_show', ['id' => $activity->getId()]);
        }

        return $this->render('organise/activity/image.html.twig', [
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
        $this->blockUnauthorisedUsers($activity->getAuthor());

        $form = $this->createDeleteForm($activity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($activity);
            $em->flush();

            return $this->redirectToRoute('organise_activity_index');
        }

        return $this->render('organise/activity/delete.html.twig', [
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
        $this->blockUnauthorisedUsers($activity->getAuthor());

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

            return $this->redirectToRoute('organise_activity_show', ['id' => $activity->getId()]);
        }

        return $this->render('organise/activity/price/new.html.twig', [
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
        $this->blockUnauthorisedUsers($price->getActivity()->getAuthor());

        $originalPrice = $price->getPrice();
        $form = $this->createForm('App\Form\Activity\PriceOptionType', $price);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (count($price->getRegistrations()) > 0 && $originalPrice < $price->getPrice()) {
                $this->addFlash('error', 'Prijs kan niet verhoogd worden als er al deelnemers geregistreerd zijn');

                return $this->render('organise/activity/price/edit.html.twig', [
                    'option' => $price,
                    'form' => $form->createView(),
                ]);
            }
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            return $this->redirectToRoute('organise_activity_show', ['id' => $price->getActivity()->getId()]);
        }

        return $this->render('organise/activity/price/edit.html.twig', [
            'option' => $price,
            'form' => $form->createView(),
        ]);
    }

    
    /**
     * Creates a form to set participent presence
     * 
     * @Route("/{id}/presence", name="presence")
     */

    public function presentEditAction(Request $request, Activity $activity)
    {
        $this->blockUnauthorisedUsers($activity->getAuthor());

        $form = $this->createForm('App\Form\Activity\ActivityEditPresent', $activity);
        $form->handleRequest($request);
    
        $em = $this->getDoctrine()->getManager();
        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Aanwezigheid aangepast');
        }

        return $this->render('organise/activity/present.html.twig', [
            'activity' => $activity,
            'form' => $form->createView()
            ]);
    }

            /**
     * Creates a form to set amount participent present
     * 
     * @Route("/{id}/setamountpresence", name="amount_present", methods={"GET", "POST"})
     */

    public function setAmountPresent(Request $request, Activity $activity)
    {
        $em = $this->getDoctrine()->getManager();

        $form = $this->createForm('App\Form\Activity\ActivitySetPresentAmount',$activity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Aanwezigen genoteerd!');
            return $this->redirectToRoute('organise_activity_show', ['id' => $activity->getId()]);
        }

        return $this->render('organise/activity/amountpresent.html.twig', [
            'activity' => $activity,
            'form' => $form->createView(),
        ]);
    }

     /**
     * Creates a form to reset amount participent present
     * 
     * @Route("/{id}/resetamountpresence", name="reset_amount_present")
     */

    public function resetAmountPresent(Request $request, Activity $activity)
    {
        $em = $this->getDoctrine()->getManager();

        $form = $this->createForm('App\Form\Activity\ActivityCountPresent',$activity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $activity->setPresent(null);
            $em->flush();
            $this->addFlash('success', 'Aanwezigen geteld!');
            return $this->redirectToRoute('organise_activity_show', ['id' => $activity->getId()]);
        }

        return $this->render('organise/activity/presentcount.html.twig', [
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
            ->setAction($this->generateUrl('organise_activity_delete', ['id' => $activity->getId()]))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
