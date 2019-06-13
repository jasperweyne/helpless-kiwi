<?php

namespace App\Controller\Admin;

use App\Template\Annotation\MenuItem;
use App\Entity\Mail\Mail;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Mail controller.
 *
 * @Route("/admin/mail", name="admin_mail_")
 */
class MailController extends AbstractController
{
    /**
     * Lists all mails.
     *
     * @MenuItem(title="Mails")
     * @Route("/", name="index", methods={"GET"})
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $mails = $em->getRepository(Mail::class)->findAll();

        return $this->render('admin/mail/index.html.twig', [
            'mails' => $mails,
        ]);
    }
}
