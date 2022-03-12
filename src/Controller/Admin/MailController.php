<?php

namespace App\Controller\Admin;

use App\Entity\Mail\Mail;
use App\Template\Annotation\MenuItem;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

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
     * @MenuItem(title="Mails", menu="admin")
     * @Route("/", name="index", methods={"GET"})
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $mails = $em->getRepository(Mail::class)->findBy([], ['sentAt' => 'DESC']);

        return $this->render('admin/mail/index.html.twig', [
            'mails' => $mails,
        ]);
    }

    /**
     * Finds and displays a mail entity.
     *
     * @Route("/{id}", name="show", methods={"GET"})
     */
    public function showAction(Mail $mail)
    {
        $content = json_decode($mail->getContent(), true);

        return $this->render('admin/mail/show.html.twig', [
            'mail' => $mail,
            'content' => $content['html'],
        ]);
    }
}
