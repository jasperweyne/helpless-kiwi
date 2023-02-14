<?php

namespace App\Controller\Admin;

use App\Entity\Mail\Mail;
use App\Template\Attribute\MenuItem;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Mail controller.
 */
#[Route("/admin/mail", name: "admin_mail_")]
class MailController extends AbstractController
{
    /**
     * Lists all mails.
     */
    #[MenuItem(title: "Mails", menu: "admin", role: "ROLE_ADMIN")]
    #[Route("/", name: "index", methods: ["GET"])]
    public function indexAction(EntityManagerInterface $em): Response
    {
        $mails = $em->getRepository(Mail::class)->findBy([], ['sentAt' => 'DESC']);

        return $this->render('admin/mail/index.html.twig', [
            'mails' => $mails,
        ]);
    }

    /**
     * Finds and displays a mail entity.
     */
    #[Route("/{id}", name: "show", methods: ["GET"])]
    public function showAction(Mail $mail): Response
    {
        $jsonContent = $mail->getContent();
        assert(is_string($jsonContent));
        $content = json_decode($jsonContent, true);
        assert(is_array($content));

        return $this->render('admin/mail/show.html.twig', [
            'mail' => $mail,
            'content' => $content['html'],
        ]);
    }
}
