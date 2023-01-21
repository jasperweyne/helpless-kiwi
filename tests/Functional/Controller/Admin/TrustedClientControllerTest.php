<?php

namespace Tests\Functional\Controller\Admin;

use App\Entity\Security\TrustedClient;
use App\Tests\AuthWebTestCase;
use App\Tests\Database\Security\LocalAccountFixture;
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
        $this->client->request('GET', $this->controllerEndpoint . "/new");
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
        $crawler = $this->client->request('GET', $this->controllerEndpoint . "/new");
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
        /* @todo This test is incomplete. */
        self::markTestIncomplete();
    }

    public function testTokenAction(): void
    {
        /* @todo This test is incomplete. */
        self::markTestIncomplete();
    }

    public function testDeleteAction(): void
    {
        /* @todo This test is incomplete. */
        self::markTestIncomplete();
    }
}
