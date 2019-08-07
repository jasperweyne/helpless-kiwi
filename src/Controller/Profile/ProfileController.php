<?php

namespace App\Controller\Profile;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Activity controller.
 *
 * @Route("/profile", name="profile_")
 */
class ProfileController extends AbstractController
{
    /**
     * Lists all activities.
     *
     * @Route("/", name="index", methods={"GET"})
     */
    public function indexAction()
    {
        $personal = $this->getUser()->getPerson();

        return $this->render('profile/index.html.twig', [
            'personal' => $personal,
        ]);
    }
}
