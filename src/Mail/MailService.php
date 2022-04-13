<?php

namespace App\Mail;

use App\Entity\Mail\Mail;
use App\Entity\Mail\Recipient;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class MailService
{
    private $mailer;

    private $em;

    private $tokenStorage;

    private $params;

    public function __construct(\Swift_Mailer $mailer, EntityManagerInterface $em, TokenStorageInterface $tokenStorage, ParameterBagInterface $params)
    {
        $this->mailer = $mailer;
        $this->em = $em;
        $this->tokenStorage = $tokenStorage;
        $this->params = $params;
    }

    public function message($to, string $title, string $body, array $attachments = [])
    {
        if (is_null($to)) {
            return;
        }

        if (!is_iterable($to)) {
            $to = [$to];
        }

        $title = ($_ENV['ORG_NAME'] ?? $this->params->get('env(ORG_NAME)')).' - '.$title;
        $from = $_ENV['DEFAULT_FROM'];
        $body_plain = html_entity_decode(strip_tags($body));

        $addresses = [];
        foreach ($to as $person) {
            if (is_null($person->getEmail())) {
                continue;
            }

            if ('' == trim($person->getName() ?? $person->getUsername() ?? '')) {
                $addresses[] = $person->getEmail();
            } else {
                $addresses[$person->getEmail()] = $person->getName() ?? $person->getUsername();
            }
        }

        $message = (new \Swift_Message($title))
            ->setFrom($from)
            ->setTo($addresses)
            ->setBody($body, 'text/html')
            ->addPart($body_plain, 'text/plain')
        ;

        $content = json_encode([
            'html' => $body,
            'plain' => $body_plain,
        ]);

        foreach ($attachments as $attachment) {
            $message->attach($attachment);
        }

        $msgEntity = new Mail();
        $msgEntity
            ->setSender($from)
            ->setPerson($this->getUser())
            ->setTitle($title)
            ->setContent($content)
            ->setSentAt(new \DateTime())
        ;
        $this->em->persist($msgEntity);

        foreach ($to as $person) {
            $recipient = new Recipient();
            $recipient
                ->setPerson($person)
                ->setMail($msgEntity)
            ;

            $this->em->persist($recipient);
        }

        $this->em->flush();

        $this->mailer->send($message);
    }

    private function getUser()
    {
        if (null === $token = $this->tokenStorage->getToken()) {
            return null;
        }

        if (!\is_object($user = $token->getUser())) {
            // e.g. anonymous authentication
            return null;
        }

        return $user;
    }
}
