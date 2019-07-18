<?php

namespace App\Controller\Admin;

use App\Template\Annotation\MenuItem;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Person\Person;

/**
 * Person controller.
 *
 * @Route("/admin/person", name="admin_person_")
 */
class PersonController extends AbstractController
{
    /**
     * Lists all Contact entities.
     *
     * @MenuItem(title="Personen", menu="admin")
     * @Route("/", name="index", methods={"GET"})
     */
    public function indexAction()
    {
        $persons = $this
            ->getDoctrine()
            ->getRepository(Person::class)
            ->findAll();

        return $this->render('admin/person/index.html.twig', [
            'persons' => $persons,
        ]);
    }
}
