<?php

namespace App\Controller\Dashboard;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class PersonalController extends AbstractController
{
    /**
     * @Route("/organise/personal", name="organise_index")
     */
    public function indexAction(Request $request)
    {
        return $this->render('admin/index.html.twig');
    }
}
