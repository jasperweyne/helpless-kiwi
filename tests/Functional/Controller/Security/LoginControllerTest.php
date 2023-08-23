<?php

namespace Tests\Functional\Controller\Security;

use App\Tests\AuthWebTestCase;
use App\Tests\Database\Security\LocalAccountFixture;
use Drenso\OidcBundle\OidcClientInterface;

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
        $this->databaseTool->loadFixtures([
            LocalAccountFixture::class,
        ]);
        $this->login();

        // Act
        $this->client->request('GET', '/login');

        // Assert
        $response = $this->client->getResponse();
        self::assertEquals(302, $response->getStatusCode());
    }

    public function testLoginOidc(): void
    {
        // Arrange
        $this->client->followRedirects(false);
        self::setupOidc(self::getContainer());

        // Act
        $this->client->request('GET', '/login');
        $address = self::unsetOidc();

        // Assert
        $response = $this->client->getResponse();
        self::assertEquals(302, $response->getStatusCode());
        self::assertIsString($response->headers->get('Location'));
        self::assertStringContainsString($address, $response->headers->get('Location'));
    }

    public function testLoginCheck(): void
    {
        // Arrange
        $this->client->followRedirects(false);

        // Act
        $this->client->request('GET', '/login_check');

        // Assert
        // Testing actual OIDC flow is unfeasible, just check the redirect to home
        self::assertEquals(302, $this->client->getResponse()->getStatusCode());
    }

    public function testLogin(): void
    {
        $crawler = $this->client->request('GET', '/login');

        $form = $crawler->selectButton('Log in')->form();
        $form['_username'] = 'wrong';
        $form['_password'] = 'login';
        $crawler = $this->client->submit($form);

        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        self::assertSelectorTextContains('.container', 'Invalid credentials.');
    }

    public function testLogout(): void
    {
        $this->client->request('GET', '/logout');

        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public static function setupOidc($container): void
    {
        // setup Kiwi side of OIDC
        $_ENV['OIDC_ADDRESS'] = 'accounts.google.com'; // use as example

        // override OidcClient configuration from assets
        $oidc = $container->get(OidcClientInterface::class);
        $refl = new \ReflectionClass($oidc);
        $prop = $refl->getProperty('configuration');
        $conf = file_get_contents(__DIR__.'/../../../assets/google-openid-configuration.json');
        $prop->setAccessible(true);
        self::assertIsString($conf);
        $prop->setValue($oidc, json_decode($conf, true));
    }

    public static function unsetOidc(): string
    {
        $address = $_ENV['OIDC_ADDRESS'];
        unset($_ENV['OIDC_ADDRESS']);

        return $address;
    }
}
