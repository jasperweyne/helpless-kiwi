<?php

namespace App\GraphQL;

use App\Entity\Security\LocalAccount;
use App\Entity\Security\TrustedClient;
use App\Repository\ApiTokenRepository;
use App\Security\LocalUserProvider;
use Doctrine\ORM\EntityManagerInterface;
use Overblog\GraphQLBundle\Annotation as GQL;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * This classes houses all of the create update and delete functions.
 */
#[GQL\Type]
#[GQL\Description("The root of all mutation operations.")]
class Mutation
{
    public function __construct(
        private EntityManagerInterface $em,
        private LocalUserProvider $userProvider,
        private ApiTokenRepository $apiTokenRepository,
        private UserPasswordHasherInterface $userPasswordHasher,
        private TokenStorageInterface $tokenStorage,
    ) {
    }

     #[GQL\Field(type: "String")]
     #[GQL\Description("Authenticate current session using username and password. Optionally generate an API token when the tokenClientSecret is provided")]
    public function login(string $username, string $password, ?string $clientSecret): ?string
    {
        // Validate that the provided client secret exists, if provided
        $client = null;
        if (null !== $clientSecret && null === $client = $this->em->getRepository(TrustedClient::class)->find($clientSecret)) {
            throw new AuthenticationException('Unknown client', Response::HTTP_FORBIDDEN);
        }

        // Load user (throws if not found)
        $user = $this->userProvider->loadUserByIdentifier($username);
        assert($user instanceof LocalAccount);

        // Validate password
        if (!$this->userPasswordHasher->isPasswordValid($user, $password)) {
            throw new AuthenticationException('Invalid credentials', Response::HTTP_FORBIDDEN);
        }

        // Set the cookie (intended for same-origin use)
        $this->tokenStorage->setToken(new UsernamePasswordToken($user, 'api', $user->getRoles()));

        // Generate an API token for trusted applications
        return $client !== null ? $this->apiTokenRepository->generate($user, $client) : null;
    }
}
