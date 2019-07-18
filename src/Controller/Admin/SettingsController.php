<?php

namespace App\Controller\Admin;

use App\Log\EventService;
use App\Template\Annotation\MenuItem;
use App\Settings\DotEnvService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Settings controller.
 *
 * @Route("/admin/settings", name="admin_settings_")
 */
class SettingsController extends AbstractController
{
    private $events;

    private $dotenv;

    public function __construct(EventService $events, DotEnvService $dotenv)
    {
        $this->events = $events;
        $this->dotenv = $dotenv;
    }

    /**
     * Lists all mails.
     *
     * @MenuItem(title="Instellingen")
     * @Route("/", name="index", methods={"GET", "POST"})
     */
    public function indexAction(Request $request)
    {
        $settings = $this->dotenv->read();

        $form = $this->createForm('App\Form\Settings\SettingsType', $settings);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $settings = $form->getData();
            $this->dotenv->write($settings);
        }
    
        return $this->render('admin/settings/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
