<?php

namespace App\Mail;

use App\Entity\Group\Relation;
use App\Entity\Group\Taxonomy;
use App\Entity\Mail\Mail;
use App\Entity\Mail\Recipient;
use App\Entity\Security\Auth;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

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

    public function message($to, string $title, string $body)
    {
        if (is_object($to)) {
            $to = [$to];
        }

        $title = ($_ENV['ORG_NAME'] ?? $this->params->get('env(ORG_NAME)')).' - '.$title;
        $from = $_ENV['DEFAULT_FROM'];
        $body_plain = html_entity_decode(strip_tags($body));

        $addresses = [];
        foreach ($to as $person) {
            if ('' == trim($person->getFullname() ?? '')) {
                $addresses[] = $person->getEmail();
            } else {
                $addresses[$person->getEmail()] = $person->getFullname();
            }
        }

        $message = (new \Swift_Message($title))
            ->setFrom($from)
            ->setTo($addresses)
            ->setBody($body, 'text/html')
            ->addPart($body_plain, 'text/plain')
        ;

        $content = serialize([
            'html' => $body,
            'plain' => $body_plain,
        ]);

        $msgEntity = new Mail();
        $msgEntity
            ->setSender($from)
            ->setAuth($this->getUser())
            ->setTitle($title)
            ->setContent($body)
            ->setSentAt(new DateTime())
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

    private function getUser(): ?Auth
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

    private function buildTaxonomy(array $to): ?Taxonomy
    {
        $recipients = new Taxonomy();

        foreach ($to as $person) {
            $recipient = new Relation();
            $recipient
                ->setTaxonomy($recipients)
                ->setPerson($person)
                
            ;

            $this->em->persist($recipient);
        }

        $emailgroup = $this->em->getRepository(Taxonomy::class)->findOneBy( ['name' => "Emails" ]);
        if ($emailgroup) {
            $recipients->setParent($emailgroup);
        } 

        return $recipients;
    }
}
