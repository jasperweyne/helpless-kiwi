<?php

namespace Tests\Functional\Controller\Admin;

use App\Controller\Admin\SecurityController;
use App\Entity\Security\LocalAccount;
use App\Log\EventService;
use App\Tests\AuthWebTestCase;
use App\Tests\Database\Security\LocalAccountFixture;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class SecurityControllerTest.
 *
 * @covers \App\Controller\Admin\SecurityController
 */
class SecurityControllerTest extends AuthWebTestCase
{
    /**
     * @var SecurityController
     */
    protected $securityController;

    /**
     * @var EventService
     */
    protected $events;

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
        $this->em = self::$container->get(EntityManagerInterface::class);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->em);
    }

    public function testGetMenuItems(): void
    {
        /* @todo This test is incomplete. */
        self::markTestIncomplete();
    }

    public function testIndexAction(): void
    {
        // Act
        $this->client->request('GET', '/admin/security/');

        // Assert
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testNewAction(): void
    {
        // Act
        $crawler = $this->client->request('GET', '/admin/security/new');
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        $form = $crawler->selectButton('Toevoegen')->form();
        $form['local_account[name]'] = 'John';
        $form['local_account[email]'] = 'john@doe.eyes';
        $crawler = $this->client->submit($form);

        // Assert
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        // TODO: figure our if this is actually the way to test this....
        self::assertEquals(1, $crawler->filter('.top > h3:nth-child(1)')->count());
    }

    public function testShowAction(): void
    {
        /* @todo This test is incomplete. */
        self::markTestIncomplete();
    }

    public function testEditAction(): void
    {
        /* @todo This test is incomplete. */
        self::markTestIncomplete();
    }

    public function testDeleteAction(): void
    {
        /* @todo This test is incomplete. */
        self::markTestIncomplete();
    }

    public function testRolesAction(): void
    {
        // Arrange
        $localAdmin = $this->em->getRepository(LocalAccount::class)->findAll()[0];
        $id = $localAdmin->getId();

        // Act
        $crawler = $this->client->request('GET', "/admin/security/{$id}/roles");
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        $form = $crawler->selectButton('Opslaan')->form();
        $form['form[admin]']->setValue(false);
        $this->client->submit($form);
        self::assertSelectorTextContains('.container', 'Rollen bewerkt');
        $localUser = $this->em->getRepository(LocalAccount::class)->findAll()[0];
        self::assertEquals(['ROLE_USER'], $localUser->getRoles());
    }
}
