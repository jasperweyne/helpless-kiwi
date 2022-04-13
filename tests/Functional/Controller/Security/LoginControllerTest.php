<?php

namespace Tests\Functional\Controller\Security;

use App\Tests\AuthWebTestCase;
use App\Tests\Database\Security\LocalAccountFixture;
use Drenso\OidcBundle\OidcClient;

/**
 * Class LoginControllerTest.
 *
 * @covers \App\Controller\Security\LoginController
 */
class LoginControllerTest extends AuthWebTestCase
{
    public function testLoginWhileLoggedIn(): void
    {
        // Arrange
        $this->client->followRedirects(false);
        $this->loadFixtures([
            LocalAccountFixture::class,
        ]);
        $this->login();

        // Act
        $this->client->request('GET', '/login');

        // Assert
        $response = $this->client->getResponse();
        $this->assertEquals(302, $response->getStatusCode());
    }

    public function testLoginOidc(): void
    {
        // Arrange
        $this->client->followRedirects(false);
        self::setupOidc(self::$container);

        // Act
        $this->client->request('GET', '/login');
        $address = self::unsetOidc();

        // Assert
        $response = $this->client->getResponse();
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertStringContainsString($address, $response->headers->get('Location'));
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

    public function testLogin(): void
    {
        $crawler = $this->client->request('GET', '/login');

        $form = $crawler->selectButton('Log in')->form();
        $form['username'] = 'wrong';
        $form['password'] = 'login';
        $crawler = $this->client->submit($form);

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('.container', 'Invalid credentials.');
    }

    public function testLogout(): void
    {
        $this->client->request('GET', '/logout');

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public static function setupOidc($container): void
    {
        // setup Kiwi side of OIDC
        $_ENV['OIDC_ADDRESS'] = 'accounts.google.com'; // use as example

        // override OidcClient configuration from assets
        $oidc = $container->get(OidcClient::class);
        $refl = new \ReflectionClass($oidc);
        $prop = $refl->getProperty('configuration');
        $conf = file_get_contents(__DIR__.'/../../../assets/google-openid-configuration.json');
        $prop->setAccessible(true);
        $prop->setValue($oidc, json_decode($conf, true));
    }

    public static function unsetOidc(): string
    {
        $address = $_ENV['OIDC_ADDRESS'];
        unset($_ENV['OIDC_ADDRESS']);

        return $address;
    }
}
