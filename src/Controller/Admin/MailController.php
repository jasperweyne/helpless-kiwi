<?php

namespace App\Controller\Admin;

use App\Entity\Mail\Mail;
use App\Template\Attribute\MenuItem;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Mail controller.
 */
#[Route('/admin/mail', name: 'admin_mail_')]
class MailController extends AbstractController
{
    /**
     * Lists all mails.
     */
    #[MenuItem(title: 'Mails', menu: 'admin', role: 'ROLE_ADMIN')]
    #[Route('/', name: 'index', methods: ['GET'])]
    public function indexAction(Request $request, EntityManagerInterface $em, PaginatorInterface $paginator): Response
    {
        $qb = $em->createQueryBuilder()
            ->select('m')
            ->from(Mail::class, 'm')
            ->orderBy('m.sentAt', 'DESC')
        ;

        $pagination = $paginator->paginate(
            $qb,
            $request->query->getInt('page', 1),
            $request->query->getInt('limit', 30)
        );

        return $this->render('admin/mail/index.html.twig', [
            'mails' => $pagination,
        ]);
    }

    /**
     * Finds and displays a mail entity.
     */
    #[Route('/{mail}', name: 'show', methods: ['GET'])]
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
