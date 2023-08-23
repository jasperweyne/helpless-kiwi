<?php

namespace Tests\Functional\Controller\Admin;

use App\Entity\Security\ApiToken;
use App\Entity\Security\LocalAccount;
use App\Entity\Security\TrustedClient;
use App\Tests\AuthWebTestCase;
use App\Tests\Database\Security\LocalAccountFixture;
use App\Tests\Database\Security\TrustedClientFixture;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DomCrawler\Field\FormField;

/**
 * Class TrustedClientControllerTest.
 *
 * @covers \App\Controller\Admin\TrustedClientController
 */
class TrustedClientControllerTest extends AuthWebTestCase
{
    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var string
     */
    private $controllerEndpoint = '/admin/security/client';

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->databaseTool->loadFixtures([
            LocalAccountFixture::class,
            TrustedClientFixture::class,
        ]);

        $this->login();
        $this->em = self::getContainer()->get(EntityManagerInterface::class);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->em);
    }

    public function testIndexAction(): void
    {
        $this->client->request('GET', $this->controllerEndpoint.'/');
        self::assertSelectorTextContains('span', 'API');
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testIndexActionNotAdmin(): void
    {
        $this->logout();
        $this->login([]);
        $this->client->request('GET', $this->controllerEndpoint.'/');
        self::assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testNewActionGet(): void
    {
        $this->client->request('GET', $this->controllerEndpoint.'/new');
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    /**
     *  @depends testNewActionGet
     */
    public function testNewActionPost(): void
    {
        // Arrange
        $originalCount = $this->em->getRepository(TrustedClient::class)->count([]);

        // Act
        $crawler = $this->client->request('GET', $this->controllerEndpoint.'/new');
        $form = $crawler->selectButton('Toevoegen')->form();
        ($field = $form['form[id]']) instanceof FormField && $field->setValue('Test');
        $this->client->submit($form);

        // Assert
        $newCount = $this->em->getRepository(TrustedClient::class)->count([]);
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        self::assertSelectorExists('.messages .flash-success');
        self::assertEquals(1, $newCount - $originalCount, "TrustedClient count didn't correctly change after POST request.");
    }

    public function testClearAction(): void
    {
        // Arrange
        $client = $this->em->getPartialReference(TrustedClient::class, TrustedClientFixture::ID);
        $account = $this->user(LocalAccountFixture::USERNAME);
        assert($account instanceof LocalAccount && null !== $client);
        $this->em->persist(new ApiToken($account, $client, new \DateTimeImmutable('+1 minutes')));
        $this->em->persist(new ApiToken($account, $client, new \DateTimeImmutable('-1 minutes')));
        $this->em->flush();
        $originalCount = $this->em->getRepository(ApiToken::class)->count([]);

        // Act
        $this->client->request('GET', $this->controllerEndpoint.'/clear');

        // Assert
        $newCount = $this->em->getRepository(ApiToken::class)->count([]);
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        self::assertSelectorExists('.messages .flash-success');
        self::assertEquals(-1, $newCount - $originalCount, "ApiToken count didn't correctly change.");
    }

    public function testTokenActionGet(): void
    {
        $id = TrustedClientFixture::ID;
        $this->client->request('GET', $this->controllerEndpoint."/$id/token");
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    /**
     *  @depends testTokenActionGet
     */
    public function testTokenActionPost(): void
    {
        // Arrange
        $originalCount = $this->em->getRepository(ApiToken::class)->count([]);
        $user = $this->user(LocalAccountFixture::USERNAME);
        assert($user instanceof LocalAccount);
        $id = TrustedClientFixture::ID;

        // Act
        $crawler = $this->client->request('GET', $this->controllerEndpoint."/$id/token");
        $form = $crawler->selectButton('Toevoegen')->form();
        ($field = $form['generate_token[account]']) instanceof FormField && $field->setValue($user->getId());
        ($field = $form['generate_token[expiresAt][date]']) instanceof FormField && $field->setValue('2013-03-15');
        ($field = $form['generate_token[expiresAt][time]']) instanceof FormField && $field->setValue('23:59');
        $this->client->submit($form);

        // Assert
        $newCount = $this->em->getRepository(ApiToken::class)->count([]);
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        self::assertSelectorExists('.messages .flash-success');
        self::assertEquals(1, $newCount - $originalCount, "ApiToken count didn't correctly change after POST request.");
    }

    public function testDeleteActionGet(): void
    {
        $id = TrustedClientFixture::ID;
        $this->client->request('GET', $this->controllerEndpoint."/$id/delete");
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    /**
     *  @depends testDeleteActionGet
     */
    public function testDeleteActionPost(): void
    {
        // Arrange
        $account = $this->user(LocalAccountFixture::USERNAME);
        assert($account instanceof LocalAccount);
        $id = 'deleter';
        $this->em->persist($client = new TrustedClient($id, 'secret'));
        $this->em->persist(new ApiToken($account, $client, new \DateTimeImmutable('+1 minutes')));
        $this->em->flush();
        $originalCountClient = $this->em->getRepository(TrustedClient::class)->count([]);
        $originalCountToken = $this->em->getRepository(ApiToken::class)->count([]);

        // Act
        $crawler = $this->client->request('GET', $this->controllerEndpoint."/$id/delete");
        $form = $crawler->selectButton('Ja, verwijder')->form();
        $this->client->submit($form);

        // Assert
        $newCountClient = $this->em->getRepository(TrustedClient::class)->count([]);
        $newCountToken = $this->em->getRepository(ApiToken::class)->count([]);
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        self::assertSelectorExists('.messages .flash-success');
        self::assertEquals(-1, $newCountClient - $originalCountClient, "TrustedClient count didn't correctly change after POST request.");
        self::assertEquals(-1, $newCountToken - $originalCountToken, "ApiToken count didn't correctly change after POST request.");
    }
}
