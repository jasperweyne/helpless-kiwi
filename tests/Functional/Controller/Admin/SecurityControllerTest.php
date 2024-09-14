<?php

namespace Tests\Functional\Controller\Admin;

use App\Controller\Admin\SecurityController;
use App\Entity\Security\LocalAccount;
use App\Log\EventService;
use App\Tests\AuthWebTestCase;
use App\Tests\Database\Security\LocalAccountFixture;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class SecurityControllerTest.
 *
 * @covers \App\Controller\Admin\SecurityController
 */
class SecurityControllerTest extends AuthWebTestCase
{
    protected SecurityController $securityController;

    protected EventService $events;

    protected EntityManagerInterface $em;

    protected string $endpoint = '/admin/security';

    protected function setUp(): void
    {
        parent::setUp();

        $this->databaseTool->loadFixtures([
            LocalAccountFixture::class,
        ]);

        $this->login();
        $this->em = self::getContainer()->get(EntityManagerInterface::class);
    }

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
        $this->client->request('GET', $this->endpoint);

        // Assert
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testImportAction(): void
    {
        // Mock the CSV file
        $csvContent = "email,given_name,family_name,admin,oidc\n";
        $csvPath = sys_get_temp_dir().'/test.csv';
        file_put_contents($csvPath, $csvContent);
        $csvFile = new UploadedFile($csvPath, 'test.csv', 'text/csv', null, true);

        // first Act
        $crawler = $this->client->request('GET', $this->endpoint.'/import');
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        $form = $crawler->selectButton('verder')->form();
        $form->setValues([
            'upload_csv[file]' => $csvFile->getPathname(),
        ]);
        $crawler = $this->client->submit($form);

        // Assert
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        self::assertSame(
            'Accounts importeren',
            $crawler->filter('h3')->first()->text()
        );

        // second Act
        $form = $crawler->selectButton('afronden')->form();
        $crawler = $this->client->submit($form);

        // second Assert
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        self::assertSelectorTextContains('.container', 'Accounts succesvol geimporteerd');
    }

    public function testNewAction(): void
    {
        // Act
        $crawler = $this->client->request('GET', $this->endpoint.'/new');
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        $form = $crawler->selectButton('Toevoegen')->form();
        $form->setValues([
            'local_account[givenname]' => 'John',
            'local_account[familyname]' => 'Doe',
            'local_account[email]' => 'john@doe.eyes',
        ]);
        $crawler = $this->client->submit($form);

        // Assert
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        // TODO: figure our if this is actually the way to test this....
        self::assertSelectorTextContains('table', 'john@doe.eyes');
    }

    public function testShowAction(): void
    {
        /* @todo This test is incomplete. */
        self::markTestIncomplete();
    }

    public function testEditAction(): void
    {
        // Setup
        $localAccount = $this->em->getRepository(LocalAccount::class)->findAll()[0];
        $id = $localAccount->getId();

        // Act
        $crawler = $this->client->request('GET', $this->endpoint.'/'.$id.'/edit');
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        $form = $crawler->selectButton('Opslaan')->form();
        $form->setValues([
            'local_account[givenname]' => 'John',
            'local_account[familyname]' => 'Doeeye',
            'local_account[email]' => 'john@doe.eyes',
        ]);
        $crawler = $this->client->submit($form);

        // Assert
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        /** @var LocalAccount $localAccount */
        $localAccount = $this->em->getRepository(LocalAccount::class)->findAll()[0];
        self::assertEquals($localAccount->getFamilyName(), 'Doeeye');
    }

    public function testDeleteAction(): void
    {
        // Setup
        $localAccount = $this->em->getRepository(LocalAccount::class)->findAll()[0];
        $id = $localAccount->getId();

        // Act
        $this->client->request('GET', $this->endpoint.'/'.$id.'/delete');
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());

        // TODO add deletion logic as soon as it's implemented
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
        $form->setValues([
            'form[admin]' => false,
        ]);
        $this->client->submit($form);
        self::assertSelectorTextContains('.container', 'Rollen bewerkt');
        $localUser = $this->em->getRepository(LocalAccount::class)->findAll()[0];
        self::assertEquals(['ROLE_USER'], $localUser->getRoles());
    }
}
