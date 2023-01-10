<?php

namespace Tests\Functional\Controller\Admin;

use App\Entity\Location\Location;
use App\Log\EventService;
use App\Tests\AuthWebTestCase;
use App\Tests\Database\Location\LocationFixture;
use App\Tests\Database\Security\LocalAccountFixture;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class LocationControllerTest.
 *
 * @covers \App\Controller\Admin\LocationController
 */
class LocationControllerTest extends AuthWebTestCase
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $em;

    /**
     * @var string
     */
    private $controllerEndpoint = '/admin/location';

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->databaseTool->loadFixtures([
            LocalAccountFixture::class,
            LocationFixture::class,
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
        self::assertSelectorTextContains('span', 'Locaties');
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testIndexActionNotAdmin(): void
    {
        $this->logout();
        $this->login(['ROLE_USER']);
        $this->client->request('GET', $this->controllerEndpoint.'/');
        self::assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testNewAction(): void
    {
        $address = 'testname';

        // Act
        $crawler = $this->client->request('GET', '/admin/location/new');

        // Act
        $form = $crawler->selectButton('Toevoegen')->form();
        $form['location[address]'] = $address;

        $crawler = $this->client->submit($form);
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        self::assertSelectorTextContains('.container', 'Locatie '.$address);
    }

    public function testShowAction(): void
    {
        /* Arrange */
        $location = $this->em->getRepository(Location::class)->findAll()[0];
        $id = $location->getId();

        // Act
        $this->client->request('GET', $this->controllerEndpoint."/{$id}/");

        // Assert
        self::assertSelectorTextContains('span', "Locatie {$location->getAddress()}");
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testEditAction(): void
    {
        // Arrange
        $location = $this->em->getRepository(Location::class)->findAll()[0];
        $id = $location->getId();
        $newName = 'Location Editing!';

        // Act
        $crawler = $this->client->request('GET', $this->controllerEndpoint."/{$id}/edit");
        $form = $crawler->selectButton('Opslaan')->form();
        $form['location[address]'] = $newName;
        $crawler = $this->client->submit($form);
        $newLocation = $this->em->getRepository(Location::class)->find($id);

        // Assert
        if (null == $newLocation) {
            self::fail();
        }
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        self::assertEquals($newName, $newLocation->getAddress());
    }

    public function testDeleteAction(): void
    {
        // Arrange
        $location = $this->em->getRepository(Location::class)->findAll()[0];
        $id = $location->getId();

        // Act
        $crawler = $this->client->request('GET', $this->controllerEndpoint."/{$id}/delete");
        $form = $crawler->selectButton('Bevestig verwijderen')->form();
        $crawler = $this->client->submit($form);

        $allLocation = $this->em->getRepository(Location::class)->findAll();

        // Assert
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        self::assertNotContains($location, $allLocation);
    }
}
