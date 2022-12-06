<?php

namespace App\GraphQL;

use App\Entity\Security\LocalAccount;
use App\Repository\ApiTokenRepository;
use App\Security\LocalUserProvider;
use Overblog\GraphQLBundle\Annotation as GQL;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * This classes houses all of the create update and delete functions.
 */
#[GQL\Type]
#[GQL\Description("The root of all mutation operations.")]
class Mutation
{
    public function __construct(
        private LocalUserProvider $userProvider,
        private ApiTokenRepository $apiTokenRepository,
        private UserPasswordHasherInterface $userPasswordHasher,
    ) {
    }

     #[GQL\Field(type: "String!")]
     #[GQL\Description("Log in using username and password.")]
    public function login(string $username, string $password): string
    {
        $user = $this->userProvider->loadUserByIdentifier($username);
        assert($user instanceof LocalAccount);

        if (!$this->userPasswordHasher->isPasswordValid($user, $password)) {
            throw new AuthenticationException('Invalid credentials', Response::HTTP_FORBIDDEN);
        }

        return $this->apiTokenRepository->generate($user);
    }
}
