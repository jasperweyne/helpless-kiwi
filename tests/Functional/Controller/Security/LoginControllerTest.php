<?php

namespace Tests\Functional\Controller\Security;

use App\Tests\AuthWebTestCase;
use Drenso\OidcBundle\OidcClient;

/**
 * Class LoginControllerTest.
 *
 * @covers \App\Controller\Security\LoginController
 */
class LoginControllerTest extends AuthWebTestCase
{
    public function testLogin(): void
    {
        /* @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testLoginOidc(): void
    {
        // Arrange
        $this->client->followRedirects(false);
        $_ENV['OIDC_ADDRESS'] = 'accounts.google.com'; // use as example

        // Force HTTPS as google won't respond to HTTP reqs
        $oidc = self::$container->get(OidcClient::class);
        $refl = new \ReflectionClass($oidc);
        $prop = $refl->getProperty('wellKnownUrl');
        $prop->setAccessible(true);
        $prop->setValue($oidc, 'https://accounts.google.com/.well-known/openid-configuration');

        // Act
        $this->client->request('GET', '/login');

        // Assert
        $response = $this->client->getResponse();
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertStringContainsString($_ENV['OIDC_ADDRESS'], $response->headers->get('Location'));
    }

    public function testLoginCheck(): void
    {
        // Arrange
        $this->client->followRedirects(false);

        // Act
        $this->client->request('GET', '/login_check');

        // Assert
        // Testing actual OIDC flow is unfeasible, just check the redirect to home
        $this->assertEquals(302, $this->client->getResponse()->getStatusCode());
    }

    public function testLogout(): void
    {
        /* @todo This test is incomplete. */
        $this->markTestIncomplete();
    }
}
