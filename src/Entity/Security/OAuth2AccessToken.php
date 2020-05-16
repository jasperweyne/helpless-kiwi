<?php

namespace App\Entity\Security;

use Doctrine\ORM\Mapping as ORM;
use League\OAuth2\Client\Token\AccessTokenInterface;
use OpenIDConnectClient\AccessToken;

/**
 * @ORM\Entity
 */
class OAuth2AccessToken
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="string", unique=true)
     */
    private $id;

    /**
     * @ORM\Column(type="json")
     */
    private $accessToken;

    /**
     * getId
     * Insert description here.
     *
     * @return
     *
     * @static
     *
     * @see
     * @since
     */
    public function getId(): string
    {
        return (string) $this->id;
    }

    /**
     * setId
     * Insert description here.
     *
     * @param string
     * @param $id
     *
     * @return
     *
     * @static
     *
     * @see
     * @since
     */
    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getAccessToken(): AccessToken
    {
        return new AccessToken(json_decode($this->accessToken, true));
    }

    /**
     * setAccessToken
     * Insert description here.
     *
     * @param string
     * @param $accessToken
     *
     * @return
     *
     * @static
     *
     * @see
     * @since
     */
    public function setAccessToken(AccessTokenInterface $accessToken): self
    {
        $this->accessToken = json_encode($accessToken);

        return $this;
    }
}
