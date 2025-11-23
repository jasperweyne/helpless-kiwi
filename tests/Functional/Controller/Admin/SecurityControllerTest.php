<?php

namespace Tests\Functional\Controller\Admin;

use App\Controller\Admin\SecurityController;
use App\Entity\Security\LocalAccount;
use App\Log\EventService;
use App\Tests\AuthWebTestCase;
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
        $csvContent = 'email,given_name,family_name,admin,oidc
        john@doe.kiwi,john,doe,User,,false';
        $csvPath = tempnam(sys_get_temp_dir(), 'csv');
        self::assertIsString($csvPath);
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
        unlink($csvPath);
    }

    public function testDuplicateEmailImportAction(): void
    {
        $csvContent = <<<CSV
email,given_name,family_name,oidc,admin
example2@user.kiwi,Example,User,,false
example@user.kiwi,Example,User,,false
example3@user.kiwi,Example,User,,false
example4@user.kiwi,Example,User,,false
example5@user.kiwi,Example,User,,false
example4@user.kiwi,Example,User,1234,false
example@user.kiwi,Example,User,1234,false 
CSV;
        // Mock the CSV file
        $csvPath = tempnam(sys_get_temp_dir(), 'csv');
        self::assertIsString($csvPath);
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
        $formFilter = $crawler->filter('body > main form[name="upload_csv"] > div');
        self::assertEquals(
            $formFilter->filter('ul > li:nth-child(1)')->text(),
            'Duplicate email "example@user.kiwi" found 2 times'
        );
        self::assertEquals(
            $formFilter->filter('ul > li:nth-child(2)')->text(),
            'Duplicate email "example4@user.kiwi" found 2 times'
        );
        self::assertEquals(
            $formFilter->filter('ul > li:nth-child(3)')->text(),
            'Duplicate oidc "1234" found 2 times'
        );
        unlink($csvPath);
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
        $localAccount = $this->em->getRepository(LocalAccount::class)->findOneBy(['email' => 'afgemeld@kiwi.nl']);
        self::assertNotNull($localAccount);
        $id = $localAccount->getId();

        // Act
        $crawler = $this->client->request('GET', $this->endpoint.'/'.$id.'/edit');
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        $form = $crawler->selectButton('Opslaan')->form();
        $form->setValues([
            'local_account[givenname]' => 'John',
            'local_account[familyname]' => 'Doeeye',
            'local_account[email]' => 'afgemeld@kiwi.nl',
        ]);
        $crawler = $this->client->submit($form);

        // Assert
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        $localAccount = $this->em->getRepository(LocalAccount::class)->findOneBy(['email' => 'afgemeld@kiwi.nl']);
        self::assertNotNull($localAccount);
        self::assertEquals('Doeeye', $localAccount->getFamilyName());
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
        /** @var LocalAccount $localAdmin */
        $localAdmin = $this->em->getRepository(LocalAccount::class)->findOneBy(['email' => 'admin@kiwi.nl']);
        $id = $localAdmin->getId();

        // Act
        $crawler = $this->client->request('GET', "/admin/security/{$id}/roles");
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        $form = $crawler->selectButton('Opslaan')->form();
        $form->setValues([
            'form[admin]' => false,
        ]);
        $this->client->submit($form);

        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        self::assertSelectorTextContains('.container', 'Rollen bewerkt');
        $localUser = $this->em->getRepository(LocalAccount::class)->findOneBy(['email' => 'admin@kiwi.nl']);
        self::assertNotNull($localUser);
        self::assertContains('ROLE_USER', $localUser->getRoles());
    }
}
