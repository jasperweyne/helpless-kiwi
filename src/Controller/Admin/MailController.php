<?php

namespace App\Controller\Admin;

use App\Template\Annotation\MenuItem;
use App\Entity\Mail\Mail;
use App\Entity\Mail\Recipient;
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
        $em = $this->getDoctrine()->getManager();

        $recipients = $em->getRepository(Recipient::class)->findBy(['mail' => $mail]);
        $content = json_decode($mail->getContent());
        $s = $content->{'html'};

        return $this->render('admin/mail/show.html.twig', [
            'mail' => $mail,
            'recipients' => $recipients,
            'content' => $s
        ]);
    }

}
