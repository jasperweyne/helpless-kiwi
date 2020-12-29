<?php

namespace Tests\Unit\Entity\Security;

use App\Entity\Security\OAuth2AccessToken;
use League\OAuth2\Client\Token\AccessTokenInterface;
use OpenIDConnectClient\AccessToken;
use ReflectionClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class OAuth2AccessTokenTest.
 *
 * @covers \App\Entity\Security\OAuth2AccessToken
 */
class OAuth2AccessTokenTest extends KernelTestCase
{
    /**
     * @var OAuth2AccessToken
     */
    protected $oAuth2AccessToken;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        /** @todo Correctly instantiate tested object to use it. */
        $this->oAuth2AccessToken = new OAuth2AccessToken();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->oAuth2AccessToken);
    }

    public function testGetId(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(OAuth2AccessToken::class))
            ->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($this->oAuth2AccessToken, $expected);
        $this->assertSame($expected, $this->oAuth2AccessToken->getId());
    }

    public function testSetId(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(OAuth2AccessToken::class))
            ->getProperty('id');
        $property->setAccessible(true);
        $this->oAuth2AccessToken->setId($expected);
        $this->assertSame($expected, $property->getValue($this->oAuth2AccessToken));
    }

    public function testGetAccessToken(): void
    {
        $expected = self::$container->get(AccessToken::class);
        $property = (new ReflectionClass(OAuth2AccessToken::class))
            ->getProperty('accessToken');
        $property->setAccessible(true);
        $property->setValue($this->oAuth2AccessToken, $expected);
        $this->assertSame($expected, $this->oAuth2AccessToken->getAccessToken());
    }

    public function testSetAccessToken(): void
    {
        $expected = self::$container->get(AccessTokenInterface::class);
        $property = (new ReflectionClass(OAuth2AccessToken::class))
            ->getProperty('accessToken');
        $property->setAccessible(true);
        $this->oAuth2AccessToken->setAccessToken($expected);
        $this->assertSame($expected, $property->getValue($this->oAuth2AccessToken));
    }
}
