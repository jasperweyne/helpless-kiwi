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
 * @author A-Daneel
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

        $this->databaseTool->loadFixtures([
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

    /**
     *   @testdox Reset action with valid token
     */
    public function testResetAction(): void
    {
        // Act
        $auth = $this->userProvider->loadUserByUsername(LocalAccountFixture::USERNAME);
        $auth->setPasswordRequestedAt(new \DateTime());
        $token = $this->passwordReset->generatePasswordRequestToken($auth);
        $crawler = $this->client->request('GET', '/password/reset/'.$auth->getId().'?token='.urlencode($token));
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());

        $form = $crawler->selectButton('Nieuw wachtwoord bevestigen')->form();
        $newPassword = 'password123';
        $form['new_password[password][first]'] = $newPassword;
        $form['new_password[password][second]'] = $newPassword;
        $crawler = $this->client->submit($form);

        // Assert
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        self::assertSelectorTextContains('.container', 'Wachtwoord aangepast!');
    }

    /**
     *   @testdox Reset action with invalid token
     */
    public function testResetWithNonValidToken(): void
    {
        // Act
        $auth = $this->userProvider->loadUserByUsername(LocalAccountFixture::USERNAME);
        $auth->setPasswordRequestedAt(new \DateTime());
        $this->passwordReset->generatePasswordRequestToken($auth);
        $this->client->request('GET', '/password/reset/'.$auth->getId().'?token='.urlencode('invalid-token'));
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        self::assertSelectorTextContains('.container', 'Invalid password token.');
    }

    /**
     *   @testdox Register action with valid token
     */
    public function testRegisterAction(): void
    {
        // Act
        $auth = $this->userProvider->loadUserByUsername(LocalAccountFixture::USERNAME);
        $auth->setPasswordRequestedAt(new \DateTime());
        $token = $this->passwordReset->generatePasswordRequestToken($auth);
        $crawler = $this->client->request('GET', '/password/register/'.$auth->getId().'?token='.urlencode($token));
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());

        $form = $crawler->selectButton('Account activeren')->form();
        $newPassword = 'password123';
        $form['new_password[password][first]'] = $newPassword;
        $form['new_password[password][second]'] = $newPassword;
        $crawler = $this->client->submit($form);

        // Assert
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        self::assertSelectorTextContains('.container', 'Account succesvol geregistreed, log in!');
    }

    /**
     *   @testdox Register action with invalid token
     */
    public function testRegisterWithNonValidToken(): void
    {
        // Act
        $auth = $this->userProvider->loadUserByUsername(LocalAccountFixture::USERNAME);
        $auth->setPasswordRequestedAt(new \DateTime());
        $this->passwordReset->generatePasswordRequestToken($auth);
        $this->client->request('GET', '/password/register/'.$auth->getId().'?token='.urlencode('invalid-token'));
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        self::assertSelectorTextContains('.container', 'Invalid password token.');
    }

    /**
     *   @testdox Request action with valid email
     */
    public function testRequestAction(): void
    {
        // Act
        $crawler = $this->client->request('GET', '/password/request');
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());

        $form = $crawler->selectButton('Verzenden')->form();
        $form['password_request[email]'] = LocalAccountFixture::USERNAME;
        $crawler = $this->client->submit($form);

        // Assert
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        self::assertSelectorTextContains('.container', 'Er is een mail met instructies gestuurd naar '.LocalAccountFixture::USERNAME);
    }

    /**
     *   @testdox Request action with invalid email
     */
    public function testRequestActionWithInvalidEmail(): void
    {
        $inValidEmail = 'this@email.isnotvalid';

        // Act
        $crawler = $this->client->request('GET', '/password/request');
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());

        $form = $crawler->selectButton('Verzenden')->form();
        $form['password_request[email]'] = $inValidEmail;
        $crawler = $this->client->submit($form);

        // Assert
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        self::assertSelectorTextContains('.container', "Er is een mail met instructies gestuurd naar ${inValidEmail}");
    }
}
