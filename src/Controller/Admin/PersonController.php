<?php

namespace App\Controller\Admin;

use App\Template\Annotation\MenuItem;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Person\Person;

/**
 * Person controller.
 *
 * @Route("/admin/person")
 */
class PersonController extends AbstractController
{
    /**
     * Lists all Contact entities.
     *
     * @MenuItem(title="Personen")
     * @Route("/", name="admin_person_index", methods={"GET"})
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
