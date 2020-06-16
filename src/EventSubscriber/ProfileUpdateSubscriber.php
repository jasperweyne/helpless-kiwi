<?php

namespace App\EventSubscriber;

use App\Security\OAuth2User;
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

        if (!$user instanceof OAuth2User) {
            return;
        }

        if (self::checkProfileUpdate($user)) {
            $event->setResponse(new RedirectResponse($this->urlGenerator->generate('profile_update')));
        }
    }

    public static function checkProfileUpdate(OAuth2User $user)
    {
        // First, check if user anonymized or pseudonymized
        if (!$person = $user->getPerson()) {
            return false;
        }

        if (is_null($person->getEmail())) {
            return true;
        }

        // Check if for any field, no value is assigned
        // if so, the profile needs to be updated
        foreach ($person->getFields() as $key => $value) {
            if (\is_null($value)) {
                return true;
            }
        }

        return false;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => 'onRequest',
        ];
    }
}
