<?php

namespace App\Mail;

use App\Entity\Mail\Mail;
use App\Entity\Mail\Recipient;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class MailService
{
    /**
     * @var MailerInterface
     */
    private $mailer;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var ParameterBagInterface
     */
    private $params;

    public function __construct(MailerInterface $mailer, EntityManagerInterface $em, TokenStorageInterface $tokenStorage, ParameterBagInterface $params)
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
                $addresses[] = new Address($person->getEmail());
            } else {
                $addresses[] = new Address($person->getEmail(), $person->getName() ?? $person->getUsername());
            }
        }

        $message = (new Email())
            ->subject($title)
            ->from($from)
            ->to(...$addresses)
            ->html($body)
            ->text($body_plain)
        ;

        $content = json_encode([
            'html' => $body,
            'plain' => $body_plain,
        ]);

        foreach ($attachments as $attachment) {
            assert($attachment instanceof Attachment);
            $message->attach($attachment->body, $attachment->filename, $attachment->mimetype);
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

    /**
     * @return UserInterface|\Stringable|null
     */
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
