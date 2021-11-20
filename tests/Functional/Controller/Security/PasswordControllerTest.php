<?php

namespace Tests\Functional\Controller\Security;

use App\Controller\Security\PasswordController;
use App\Security\LocalUserProvider;
use App\Security\PasswordResetService;
use App\Tests\AuthWebTestCase;
use App\Tests\Database\Security\LocalAccountFixture;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Class PasswordControllerTest.
 *
 * @covers \App\Controller\Security\PasswordController
 */
class PasswordControllerTest extends AuthWebTestCase
{
    /**
     * @var PasswordController
     */
    protected $passwordController;

    /**
     * @var UserPasswordEncoderInterface
     */
    protected $passwordEncoder;

    /**
     * @var PasswordResetService
     */
    protected $passwordReset;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        $this->passwordEncoder = self::$container->get(UserPasswordEncoderInterface::class);
        $this->passwordReset = self::$container->get(PasswordResetService::class);
        $this->passwordController = new PasswordController($this->passwordEncoder, $this->passwordReset);
        $this->em = self::$container->get(EntityManagerInterface::class);
        $this->userProvider = new LocalUserProvider($this->em);

        $this->loadFixtures([
            LocalAccountFixture::class,
        ]);

    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->passwordController);
        unset($this->passwordEncoder);
        unset($this->passwordReset);
        unset($this->userProvider);
        unset($this->em);
    }

    public function testResetAction(): void
    {
        // Act
        $auth = $this->userProvider->loadUserByUsername(LocalAccountFixture::USERNAME);
        $auth->setPasswordRequestedAt(new \DateTime());
        $token = $this->passwordReset->generatePasswordRequestToken($auth);
        $crawler = $this->client->request('GET', '/password/reset/' . $auth->getId() . '?token=' . $token);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $form = $crawler->selectButton('Nieuw wachtwoord bevestigen')->form();
        $newPassword = 'password123';
        $form['new_password[password][first]'] = $newPassword;
        $form['new_password[password][second]'] = $newPassword;
        $crawler = $this->client->submit($form);

        // Assert
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('.container', 'Wachtwoord aangepast!');
    }

    public function testRegisterAction(): void
    {
        // Act
        $auth = $this->userProvider->loadUserByUsername(LocalAccountFixture::USERNAME);
        $auth->setPasswordRequestedAt(new \DateTime());
        $token = $this->passwordReset->generatePasswordRequestToken($auth);
        $crawler = $this->client->request('GET', '/password/register/' . $auth->getId() . '?token=' . $token);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $form = $crawler->selectButton('Account activeren')->form();
        $newPassword = 'password123';
        $form['new_password[password][first]'] = $newPassword;
        $form['new_password[password][second]'] = $newPassword;
        $crawler = $this->client->submit($form);

        // Assert
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('.container', 'Account succesvol geregistreed, log in!');
    }

    public function testRequestAction(): void
    {
        // Act
        $crawler = $this->client->request('GET', '/password/request');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $form = $crawler->selectButton('Verzenden')->form();
        $form['password_request[email]'] = 'admin@test.nl';
        $crawler = $this->client->submit($form);

        // Assert
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('.container', 'Er is een mail met instructies gestuurd naar ' . LocalAccountFixture::USERNAME);
    }
}
