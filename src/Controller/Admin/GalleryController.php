<?php

namespace App\Controller\Admin;

use App\Entity\Gallery\Photo;
use App\Form\Gallery\PhotoType;
use App\Repository\GroupRepository;
use App\Template\Attribute\MenuItem;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Gallery controller.
 */
#[Route("/admin/gallery", name: "admin_gallery_")]
class GalleryController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em
    ) {
    }

    /**
     * Lists all activities.
     */
    #[MenuItem(title: "Gallerij", menu: "admin", activeCriteria: "admin_gallery_")]
    #[Route("/", name: "index", methods: ["GET"])]
    public function indexAction(): Response
    {
        $photos = $this->em->getRepository(Photo::class)->findAll();

        return $this->render('admin/gallery/index.html.twig', [
            'photos' => $photos,
        ]);
    }

    /**
     * Creates a new activity entity.
     */
    #[Route("/new", name: "new", methods: ["GET", "POST"])]
    public function newAction(Request $request): Response
    {
        $photo = new Photo();

        $form = $this->createForm(PhotoType::class, $photo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($photo);
            $this->em->flush();

            return $this->redirectToRoute('admin_gallery_show', ['id' => $photo->getId()]);
        }

        return $this->render('admin/gallery/new.html.twig', [
            'photo' => $photo,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Finds and displays a activity entity.
     */
    #[Route("/{id}", name: "show", methods: ["GET"])]
    public function showAction(Photo $photo): Response
    {
        return $this->render('admin/gallery/show.html.twig', [
            'photo' => $photo,
        ]);
    }

    /**
     * Displays a form to edit an existing activity entity.
     */
    #[Route("/{id}/edit", name: "edit", methods: ["GET", "POST"])]
    public function editAction(Request $request, Photo $photo, GroupRepository $groupRepo): Response
    {
        $form = $this->createForm(PhotoType::class, $photo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();

            return $this->redirectToRoute('admin_gallery_show', ['id' => $photo->getId()]);
        }

        return $this->render('admin/gallery/edit.html.twig', [
            'photo' => $photo,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Deletes a ApiKey entity.
     */
    #[Route("/{id}/delete", name: "delete")]
    public function deleteAction(Request $request, Photo $photo): Response
    {
        $form = $this->createDeleteForm($photo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->remove($photo);
            $this->em->flush();

            return $this->redirectToRoute('admin_photo_index');
        }

        return $this->render('admin/gallery/delete.html.twig', [
            'photo' => $photo,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Creates a form to check out all checked in users.
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Photo $photo): FormInterface
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_gallery_delete', ['id' => $photo->getId()]))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
