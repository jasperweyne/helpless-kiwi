<?php

namespace App\Provider;

use App\Provider\Person\PersonProviderInterface;
use App\Entity\Security\OAuth2AccessToken;
use App\Provider\Person\Person;
use App\Security\OAuth2User;
use Doctrine\ORM\EntityManagerInterface;
use OpenIDConnectClient\OpenIDConnectProvider;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class BunnyDataProvider implements PersonProviderInterface
{
    protected $provider;

    protected $tokens;

    protected $em;

    private $cache;

    public function __construct(OpenIDConnectProvider $provider, TokenStorageInterface $tokens, EntityManagerInterface $em)
    {
        $this->provider = $provider;
        $this->tokens = $tokens;
        $this->em = $em;
        $this->cache = null;
    }

    public function findPerson(string $id): ?Person
    {
        foreach ($this->findPersons() as $person) {
            if ($person->getId() == $id) {
                return $person;
            }
        }

        return null;
    }

    public function findPersons(): array
    {
        if (is_null($this->cache)) {
            $user = $this->tokens->getToken()->getUser();
            if (!$user instanceof OAuth2User)
                return [];

            $accessToken = $this->em->getRepository(OAuth2AccessToken::class)->find($user->getId());
            
            $request = $this->provider->getAuthenticatedRequest(
                'GET',
                'http://localhost:4000/api/person',
                $accessToken->getAccessToken()
            );

            $response = $this->provider->getParsedResponse($request);

            $this->cache = [];
            foreach ($response as $data) {
                $person = new Person();
                $person
                    ->setId($data['id'])
                    ->setEmail($data['email'] ?? null)
                    ->setFields($data)
                ;

                $this->cache[] = $person;
            }
        }

        return $this->cache;
    }
}