<?php

namespace App\GraphQL;

use App\Entity\Security\ApiToken;
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
        $clientRepository = $this->em->getRepository(TrustedClient::class);
        $client = null;
        if (null !== $clientSecret && null === $client = $clientRepository->findOneBy(['secret' => $clientSecret])) {
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

    #[GQL\Field(type: "Null")]
    #[GQL\Description("Invalidate the current session. Optionally revoke an API token when the tokenString is provided")]
    public function logout(?string $tokenString): void
    {
        if (null !== $tokenString) {
            // Check if the token exists
            if (null === $token = $this->em->getRepository(ApiToken::class)->find($tokenString)) {
                throw new AuthenticationException('Unknown token', Response::HTTP_FORBIDDEN);
            }

            // Validate that the current user is authorized (provided through session or HTTP header)
            $sessionToken = $this->tokenStorage->getToken();
            $currentUser = $sessionToken !== null ? $sessionToken->getUser() : null;
            if ($token->account !== $currentUser && $currentUser !== null && !in_array('ROLE_ADMIN', $currentUser->getRoles(), true)) {
                throw new AuthenticationException('Not authorized to invalidate user', Response::HTTP_UNAUTHORIZED);
            }

            // Remove the token
            $this->em->remove($token);
            $this->em->flush();
        }

        // Unset the cookie, invalidating the current session (intended for same-origin use)
        $this->tokenStorage->setToken(null);
    }
}
