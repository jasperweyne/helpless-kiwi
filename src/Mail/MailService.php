<?php

namespace App\Mail;

use App\Entity\Mail\Mail;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class MailService
{
    private $mailer;

    private $em;

    private $tokenStorage;

    public function __construct(\Swift_Mailer $mailer, EntityManagerInterface $em, TokenStorageInterface $tokenStorage)
    {
        $this->mailer = $mailer;
        $this->em = $em;
        $this->tokenStorage = $tokenStorage;
    }

    public function message(string $to, string $title, string $body)
    {
        $message = (new \Swift_Message($title))
            ->setFrom($_ENV['DEFAULT_FROM'])
            ->setTo($to)
            ->setBody($body, 'text/html')
            ->addPart(html_entity_decode(strip_tags($body)), 'text/plain')
        ;

        $user = null;
        if (null == $token = $this->tokenStorage->getToken()) {
            if (!\is_object($user = $token->getUser())) {
                // e.g. anonymous authentication
                $user = null;
            }
        }

        $msgEntity = new Mail();
        $msgEntity
            ->setAuth($user)
            ->setTitle($title)
            ->setContent($body)
        ;

        $this->em->persist($msgEntity);
        $this->em->flush();

        $this->mailer->send($message);
    }
}
