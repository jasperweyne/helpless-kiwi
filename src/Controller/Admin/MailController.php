<?php

namespace App\Controller\Admin;

use App\Template\Annotation\MenuItem;
use App\Entity\Mail\Mail;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Mail controller.
 *
 * @Route("/admin/mail")
 */
class MailController extends AbstractController
{
    /**
     * Lists all mails.
     *
     * @MenuItem(title="Mails")
     * @Route("/", name="admin_mail_index", methods={"GET"})
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
