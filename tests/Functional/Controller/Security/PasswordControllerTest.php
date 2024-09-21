<?php

namespace Tests\Functional\Controller\Security;

use App\Controller\Security\PasswordController;
use App\Entity\Security\LocalAccount;
use App\Security\LocalUserProvider;
use App\Security\PasswordResetService;
use App\Tests\AuthWebTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Class PasswordControllerTest.
 *
 * @author A-Daneel
 *
 * @covers \App\Controller\Security\PasswordController
 */
class PasswordControllerTest extends AuthWebTestCase
{
    protected EntityManagerInterface $em;
    protected PasswordController $passwordController;
    protected UserPasswordHasherInterface $passwordHasher;
    protected PasswordResetService $passwordReset;
    protected LocalUserProvider $userProvider;
    protected EventDispatcherInterface $dispatcher;

    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        $this->passwordHasher = self::getContainer()->get(UserPasswordHasherInterface::class);
        $this->passwordReset = self::getContainer()->get(PasswordResetService::class);
        $this->em = self::getContainer()->get(EntityManagerInterface::class);
        $this->dispatcher = self::getContainer()->get(EventDispatcherInterface::class);
        $this->passwordController = new PasswordController($this->passwordHasher, $this->passwordReset, $this->em);
        $this->userProvider = new LocalUserProvider($this->em, $this->dispatcher);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->passwordController);
        unset($this->passwordHasher);
        unset($this->passwordReset);
        unset($this->userProvider);
        unset($this->em);
        unset($this->dispatcher);
    }

    /**
     *   @testdox Reset action with valid token
     */
    public function testResetAction(): void
    {
        // Act
        $auth = $this->userProvider->loadUserByIdentifier('admin@kiwi.nl');
        assert($auth instanceof LocalAccount);
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
        $auth = $this->userProvider->loadUserByIdentifier('admin@kiwi.nl');
        assert($auth instanceof LocalAccount);
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
        $auth = $this->userProvider->loadUserByIdentifier('admin@kiwi.nl');
        assert($auth instanceof LocalAccount);
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
        $auth = $this->userProvider->loadUserByIdentifier('admin@kiwi.nl');
        assert($auth instanceof LocalAccount);
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
        $form['password_request[email]'] = 'admin@kiwi.nl';
        $crawler = $this->client->submit($form);

        // Assert
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        self::assertSelectorTextContains('.container', 'Er is een mail met instructies gestuurd naar');
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
        self::assertSelectorTextContains('.container', "Er is een mail met instructies gestuurd naar {$inValidEmail}");
    }
}
