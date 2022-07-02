<?php

namespace App\Controller\Admin;

use App\Entity\Location\Location;
use App\Log\Doctrine\EntityNewEvent;
use App\Log\Doctrine\EntityUpdateEvent;
use App\Log\EventService;
use App\Template\Annotation\MenuItem;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Location controller.
 *
 * @Route("/admin/location", name="admin_location_")
 */
class LocationController extends AbstractController
{
    /**
     * @var EventService
     */
    private $events;

    public function __construct(EventService $events)
    {
        $this->events = $events;
    }

    /**
     * Lists all locations.
     *
     * @MenuItem(title="Locaties", menu="admin", activeCriteria="admin_location_")
     * @Route("/", name="index", methods={"GET"})
     */
    public function indexAction(): Response
    {
        $em = $this->getDoctrine()->getManager();

        $locations = $em->getRepository(Location::class)->findAll();

        return $this->render('admin/location/index.html.twig', [
            'locations' => $locations,
        ]);
    }

    /**
     * Finds and displays a location entity.
     *
     * @Route("/{id}", name="show", methods={"GET"})
     */
    public function showAction(Location $location): Response
    {
        $createdAt = $this->events->findOneBy($location, EntityNewEvent::class);
        $modifs = $this->events->findBy($location, EntityUpdateEvent::class);

        return $this->render('admin/location/show.html.twig', [
            'createdAt' => $createdAt,
            'modifs' => $modifs,
            'location' => $location,
        ]);
    }

    /**
     * Displays a form to edit an existing location entity.
     *
     * @Route("/{id}/edit", name="edit", methods={"GET", "POST"})
     */
    public function editAction(Request $request, Location $location): Response
    {
        $form = $this->createForm('App\Form\Location\Location', $location);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('admin_location_show', ['id' => $location->getId()]);
        }

        return $this->render('admin/location/edit.html.twig', [
            'location' => $location,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Deletes a location entity.
     *
     * @Route("/{id}/delete", name="delete")
     */
    public function deleteAction(Request $request, Location $location): Response
    {
        $form = $this->createDeleteForm($location);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($location);
            $em->flush();

            return $this->redirectToRoute('admin_location_index');
        }

        return $this->render('admin/location/delete.html.twig', [
            'location' => $location,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Creates a form to delete a location.
     *
     * @return \Symfony\Component\Form\FormInterface The form
     */
    private function createDeleteForm(Location $location): FormInterface
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_location_delete', ['id' => $location->getId()]))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
