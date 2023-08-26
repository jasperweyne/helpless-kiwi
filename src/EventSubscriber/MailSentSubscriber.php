<?php

namespace App\EventSubscriber;

use App\Entity\Mail\Mail;
use App\Entity\Mail\Recipient;
use App\Entity\Security\LocalAccount;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\BodyRenderer;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\Event\MessageEvent;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class MailSentSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private TokenStorageInterface $tokenStorage,
        private ParameterBagInterface $params,
        private BodyRenderer $renderer,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        // return the subscribed events, their methods and priorities
        return [
            MessageEvent::class => [
                ['storeEmail', -100],
            ],
        ];
    }

    public function storeEmail(MessageEvent $event): void
    {
        $message = $event->getMessage();
        assert($message instanceof Email);

        // Decode email data
        $author = $this->getUser();
        $content = json_encode([
            'html' => $message->getHtmlBody(),
            'plain' => $message->getTextBody(),
        ]);

        assert($author instanceof LocalAccount);
        assert(false !== $content);

        // Construct mail entity
        $msgEntity = new Mail();
        $msgEntity
            ->setSender(implode('; ', $this->emails($message->getFrom())))
            ->setPerson($this->getUser())
            ->setTitle($message->getSubject())
            ->setContent($content)
            ->setSentAt(new \DateTime());
        $this->em->persist($msgEntity);

        $to = $this->em->getRepository(LocalAccount::class)->findBy(['email' => $this->emails($message->getTo())]);
        foreach ($to as $person) {
            $recipient = new Recipient();
            $recipient
                ->setPerson($person)
                ->setMail($msgEntity);

            $this->em->persist($recipient);
        }

        $this->em->flush();
    }

    /**
     * @return UserInterface|null
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

    private function emails(array $addresses): array
    {
        return array_map(fn (Address $address) => $address->getAddress(), $addresses);
    }
}
