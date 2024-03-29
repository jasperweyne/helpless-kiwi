<?php

namespace App\GraphQL;

use App\Entity\Security\ApiToken;
use App\Entity\Security\LocalAccount;
use App\Entity\Security\TrustedClient;
use App\Repository\ApiTokenRepository;
use App\Security\Authenticator\InternalCredentialsAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use GraphQL\Error\UserError;
use Overblog\GraphQLBundle\Annotation as GQL;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;

/**
 * This classes houses all of the create update and delete functions.
 */
#[GQL\Type]
#[GQL\Description('The root of all mutation operations.')]
class Mutation
{
    public function __construct(
        private EntityManagerInterface $em,
        private ApiTokenRepository $apiTokenRepository,
        private TokenStorageInterface $tokenStorage,
        private EventDispatcherInterface $dispatcher,
        private InternalCredentialsAuthenticator $authenticator,
        private RequestStack $requestStack,
        private PasswordHasherFactoryInterface $factory,
    ) {
    }

    #[GQL\Field(type: 'String')]
    #[GQL\Description('Generate an API token based on user credentials')]
    public function login(string $clientId, string $clientSecret, string $username, string $password): string
    {
        // Validate that the provided client secret exists, if provided
        $clientRepository = $this->em->getRepository(TrustedClient::class);
        $hasher = $this->factory->getPasswordHasher(TrustedClient::class);
        if (null === ($client = $clientRepository->find($clientId)) || !$hasher->verify($client->secret, $clientSecret)) {
            throw new UserError('Unknown client', Response::HTTP_FORBIDDEN);
        }

        // Store credentials in request
        $request = $this->requestStack->getCurrentRequest();
        assert(null !== $request);
        InternalCredentialsAuthenticator::provideCredentials($request, $username, $password);

        // Validate credentials
        try {
            $passport = $this->authenticator->authenticate($request);
            $this->dispatcher->dispatch(new CheckPassportEvent($this->authenticator, $passport));
            foreach ($passport->getBadges() as $badge) {
                if (!$badge->isResolved()) {
                    throw new BadCredentialsException('Not all security badges were resolved');
                }
            }
        } catch (BadCredentialsException) {
            throw new UserError('Invalid credentials');
        }

        // Generate and return a new API token
        $user = $passport->getUser();
        assert($user instanceof LocalAccount);

        return $this->apiTokenRepository->generate($user, $client);
    }

    #[GQL\Field(type: 'Null')]
    #[GQL\Description('Revoke an API token')]
    public function logout(string $tokenString): void
    {
        // Check if currently a user is authenticated
        if (null === ($sessionToken = $this->tokenStorage->getToken()) || null === ($currentUser = $sessionToken->getUser())) {
            throw new UserError('Not authorized to revoke tokens', Response::HTTP_UNAUTHORIZED);
        }

        // Check if the token exists
        if (null === $token = $this->em->getRepository(ApiToken::class)->find($tokenString)) {
            throw new UserError('Unknown token', Response::HTTP_NOT_FOUND);
        }

        // Validate that the current user is authorized (provided through session or HTTP header)
        if ($token->account !== $currentUser && !in_array('ROLE_ADMIN', $currentUser->getRoles(), true)) {
            throw new UserError('Not authorized to invalidate user', Response::HTTP_FORBIDDEN);
        }

        // Remove the token
        $this->em->remove($token);
        $this->em->flush();
    }
}
