<?php

namespace App\Provider;

use App\Entity\Security\OAuth2AccessToken;
use App\Provider\Person\Person;
use App\Provider\Person\PersonProviderInterface;
use App\Security\OAuth2User;
use App\Security\OAuth2UserProvider;
use Doctrine\ORM\EntityManagerInterface;
use OpenIDConnectClient\OpenIDConnectProvider;
use Psr\Http\Message\RequestInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class BunnyDataProvider implements PersonProviderInterface
{
    protected $provider;

    protected $tokens;

    protected $em;

    protected $userProvider;

    private $cache;

    public function __construct(OpenIDConnectProvider $provider, TokenStorageInterface $tokens, EntityManagerInterface $em, OAuth2UserProvider $userProvider)
    {
        $this->provider = $provider;
        $this->tokens = $tokens;
        $this->em = $em;
        $this->userProvider = $userProvider;
        $this->cache = null;
    }

    public function getAddress(): ?string
    {
        if (!isset($_ENV['BUNNY_ADDRESS'])) {
            return null;
        }

        return ($_ENV['SECURE_SCHEME'] ?? 'https').'://'.$_ENV['BUNNY_ADDRESS'];
    }

    public function getRequest(string $method, string $uri, array $options = []): ?RequestInterface
    {
        $user = $this->tokens->getToken()->getUser();
        if (!$user instanceof OAuth2User || is_null($this->getAddress())) {
            return null;
        }

        $dbToken = $this->em->getRepository(OAuth2AccessToken::class)->find($user->getId());
        $accessToken = $dbToken->getAccessToken();

        if ($accessToken->hasExpired()) {
            try {
                $user = $this->userProvider->refreshUser($user);
            } catch (\Exception $e) {
                return null;
            }

            $dbToken = $this->em->getRepository(OAuth2AccessToken::class)->find($user->getId());
            $accessToken = $dbToken->getAccessToken();
        }

        return $this->provider->getAuthenticatedRequest($method, $this->getAddress().$uri, $accessToken, $options);
    }

    public function findPerson(string $id): ?Person
    {
        if (is_null($this->cache)) {
            $this->findPersons();
        }

        foreach ($this->cache as $person) {
            if ($person->getId() == $id) {
                return $person;
            }
        }

        return null;
    }

    public function findPersonByEmail(string $email): ?Person
    {
        if (is_null($this->cache)) {
            $this->findPersons();
        }

        foreach ($this->cache as $person) {
            if ($person->getEmail() == $email) {
                return $person;
            }
        }

        return null;
    }

    public function findPersons(): array
    {
        if (is_null($this->cache)) {
            $this->cache = [];
            $request = $this->getRequest('GET', '/api/person/');

            if (!is_null($request)) {
                try {
                    $response = $this->provider->getParsedResponse($request);
                } catch (\Exception $e) {
                    // refresh and retry
                    $this->userProvider->refreshUser($this->tokens->getToken()->getUser());

                    $request = $this->getRequest('GET', '/api/person/');
                    $response = $this->provider->getParsedResponse($request);
                }

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
        }

        return $this->cache;
    }
}
