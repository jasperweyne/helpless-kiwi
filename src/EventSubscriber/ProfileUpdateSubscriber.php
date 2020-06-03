<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ProfileUpdateSubscriber implements EventSubscriberInterface
{
    private $tokenStorage;

    private $urlGenerator;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->urlGenerator = $urlGenerator;
    }

    public function onRequest(RequestEvent $event)
    {
        return;
        
        $request = $event->getRequest();
        if ($request->hasPreviousSession()) {
            return;
        }

        if (!$event->isMasterRequest()) {
            return;
        }

        if (!$token = $this->tokenStorage->getToken()) {
            return;
        }

        if (!$token->isAuthenticated()) {
            return;
        }

        if (!$user = $token->getUser()) {
            return;
        }

        if (!$user instanceof LocalAccount) {
            return;
        }

        if (self::checkProfileUpdate($user)) {
            $event->setResponse(new RedirectResponse($this->urlGenerator->generate('profile_update')));
        }
    }

    public static function checkProfileUpdate(LocalAccount $user)
    {
        // First, check if user anonymized or pseudonymized
        if (!$person = $user->getPerson()) {
            return false;
        }

        if (is_null($person->getEmail())) {
            return false;
        }

        // Check if for any field, no PersonValue is assigned
        // if so, the profile needs to be updated
        return $person->getKeyValues()->exists(function ($key, $x) { return is_null($x['value']); });
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => 'onRequest',
        ];
    }
}
