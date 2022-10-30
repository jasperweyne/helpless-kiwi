<?php

namespace App\Controller\Admin;

use App\Entity\Location\Location;
use App\Form\Location\LocationType;
use App\Log\Doctrine\EntityNewEvent;
use App\Log\Doctrine\EntityUpdateEvent;
use App\Log\EventService;
use App\Template\Annotation\MenuItem;
use Doctrine\ORM\EntityManagerInterface;
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
    public function __construct(
        private EventService $events,
        private EntityManagerInterface $em
    ) {
    }

    /**
     * Lists all location entities.
     *
     * @MenuItem(title="Locaties", menu="admin", activeCriteria="admin_location_")
     * @Route("/", name="index", methods={"GET", "POST"})
     */
    public function indexAction(): Response
    {
        $locations = $this->em->getRepository(Location::class)->findBy([], ['address' => 'ASC']);

        return $this->render('admin/location/index.html.twig', [
            'locations' => $locations,
        ]);
    }

    /**
     * Creates a new location.
     *
     * @Route("/new", name="new", methods={"GET", "POST"})
     */
    public function newAction(Request $request): Response
    {
        $location = new Location();

        $form = $this->createForm(LocationType::class, $location);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($location);
            $this->em->flush();

            return $this->redirectToRoute('admin_location_show', ['id' => $location->getId()]);
        }

        return $this->render('admin/location/new.html.twig', [
            'location' => $location,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Finds and displays an auth entity.
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
     * Displays a form to edit an existing activity entity.
     *
     * @Route("/{id}/edit", name="edit", methods={"GET", "POST"})
     */
    public function editAction(Request $request, Location $location): Response
    {
        $form = $this->createForm(LocationType::class, $location);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();

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
            $this->em->remove($location);
            $this->em->flush();

            return $this->redirectToRoute('admin_location_index');
        }

        return $this->render('admin/location/delete.html.twig', [
            'location' => $location,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Creates a form to check out all checked in users.
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
