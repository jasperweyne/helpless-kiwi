<?php

namespace App\Controller\Admin;

use App\Entity\Activity\Activity;
use App\Entity\Group\Group;
use App\Entity\Location\Location;
use App\Entity\Location\Note;
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
    /**
     * @var EventService
     */
    private $events;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(EventService $events, EntityManagerInterface $em)
    {
        $this->events = $events;
        $this->em = $em;
    }

    /**
     * Lists all locations.
     *
     * @MenuItem(title="Locaties", menu="admin", activeCriteria="admin_location_")
     * @Route("/", name="index", methods={"GET"})
     */
    public function indexAction(): Response
    {
        $locations = $this->em->getRepository(Location::class)->findAll();

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
            $this->em->flush();

            return $this->redirectToRoute('admin_location_show', ['id' => $location->getId()]);
        }

        return $this->render('admin/location/edit.html.twig', [
            'location' => $location,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Merges other locations into this one a location entity.
     *
     * @Route("/{id}/deduplicate", name="dedupe", methods={"GET", "POST"})
     */
    public function dedupeAction(Request $request, Location $location): Response
    {
        $locations = $this->em->getRepository(Location::class)->findAll();
        if (($key = array_search($location, $locations, true)) !== false) {
            unset($locations[$key]);
        }

        $form = $this->createForm('App\Form\Location\DedupeLocationsType', [], ['locations' => $locations]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            foreach ((array) $form->getData() as $id => $dedupe) {
                if ($dedupe === true) {
                    // update activities
                    /** @var Activity[] */
                    $activities = $this->em->getRepository(Activity::class)->findBy([
                        'location' => $id,
                    ]);
                    foreach ($activities as $activity) {
                        $activity->setLocation($location);
                    }

                    // update notes
                    /** @var Note[] */
                    $notes = $this->em->getRepository(Note::class)->findBy([
                        'location' => $id,
                    ]);
                    foreach ($notes as $note) {
                        $note->setLocation($location);
                    }

                    // remove the location
                    $this->em->remove($this->em->getPartialReference(Location::class, $id));
                }
            }
            $this->em->flush();

            return $this->redirectToRoute('admin_location_show', ['id' => $location->getId()]);
        }

        return $this->render('admin/location/dedupe.html.twig', [
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
            ;
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
     * Finds and displays a activity entity.
     *
     * @Route("/{id}/note/new/", name="note_new", methods={"GET", "POST"})
     */
    public function noteNewAction(Request $request, Location $location): Response
    {
        $note = new Note();
        $note->setLocation($location);

        $groups = [];
        if ($this->isGranted('ROLE_ADMIN')) {
            $groups = $this->em->getRepository(Group::class)->findAll();
            $groups[] = null;
        } else {
            $groups = $this->em->getRepository(Group::class)->findAllFor($this->getUser());
        }

        $form = $this->createForm('App\Form\Location\NoteType', $note, ['groups' => $groups]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($note);
            $this->em->flush();

            return $this->redirectToRoute('admin_location_show', ['id' => $location->getId()]);
        }

        return $this->render('admin/location/note/new.html.twig', [
            'location' => $location,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Finds and displays a activity entity.
     *
     * @Route("/note/{id}", name="note_edit", methods={"GET", "POST"})
     */
    public function noteEditAction(Request $request, Note $note): Response
    {
        $groups = [];
        if ($this->isGranted('ROLE_ADMIN')) {
            $groups = $this->em->getRepository(Group::class)->findAll();
            $groups[] = null;
        }

        $form = $this->createForm('App\Form\Location\NoteType', $note, ['groups' => $groups]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();

            return $this->redirectToRoute('admin_location_show', ['id' => $note->getLocation()->getId()]);
        }

        return $this->render('admin/location/note/edit.html.twig', [
            'note' => $note,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Delete a note entity.
     *
     * @Route("/note/{id}/delete", name="note_delete", methods={"GET", "DELETE"})
     */
    public function noteDeleteAction(Request $request, Note $note): Response
    {
        $form = $this->createNoteDeleteForm($note);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->remove($note);
            $this->em->flush();

            return $this->redirectToRoute('admin_location_show', ['id' => $note->getLocation()->getId()]);
        }

        return $this->render('admin/location/note/delete.html.twig', [
            'note' => $note,
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

    /**
     * Creates a form to check out all checked in users.
     *
     * @return \Symfony\Component\Form\FormInterface The form
     */
    private function createNoteDeleteForm(Note $note): FormInterface
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_location_note_delete', ['id' => $note->getId()]))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
