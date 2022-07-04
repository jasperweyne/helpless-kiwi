<?php

namespace Tests\Functional\Controller\Admin;

use App\Entity\Group\Group;
use App\Entity\Group\Relation;
use App\Tests\AuthWebTestCase;
use App\Tests\Database\Group\GroupFixture;
use App\Tests\Database\Group\RelationFixture;
use App\Tests\Database\Security\LocalAccountFixture;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class GroupControllerTest.
 *
 * @covers \App\Controller\Admin\GroupController
 */
class GroupControllerTest extends AuthWebTestCase
{
    /**
     * @var EntityManagerInterface
     */
    protected $em;

    private $controllerEndpoint = '/admin/group';

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadFixtures([
            LocalAccountFixture::class,
            RelationFixture::class,
            GroupFixture::class,
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

    public function testNewAction(): void
    {
        // Arrange
        $newName = 'Bestuur Wispeltuur';

        // Act
        $crawler = $this->client->request('GET', $this->controllerEndpoint.'/new');
        $form = $crawler->selectButton('Toevoegen')->form();
        $form['group[name]'] = $newName;
        $crawler = $this->client->submit($form);
        $allGroups = $this->em->getRepository(Group::class)->findAll();
        $newGroupId = explode('group/', $crawler->getUri() ?? '')[1];
        $newGroup = $this->em->getRepository(Group::class)->find($newGroupId);

        // Assert
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        self::assertContains($newGroup, $allGroups);
    }

    public function testShowAction(): void
    {
        $this->client->request('GET', $this->controllerEndpoint.'/');
        self::assertSelectorTextContains('span', 'Groepen');
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testEditAction(): void
    {
        // Arrange
        $group = $this->em->getRepository(Group::class)->findAll()[0];
        $id = $group->getId();
        $newName = 'Bestuur Wispeltuur';

        // Act
        $crawler = $this->client->request('GET', $this->controllerEndpoint."/{$id}/edit");
        $form = $crawler->selectButton('Opslaan')->form();
        $form['group[name]'] = $newName;
        $crawler = $this->client->submit($form);
        $newGroup = $this->em->getRepository(Group::class)->findAll()[0];

        // Assert
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        self::assertEquals($newName, $newGroup->getName());
    }

    public function testDeleteAction(): void
    {
        // Arrange
        $group = $this->em->getRepository(Group::class)->findAll()[0];
        $id = $group->getId();

        // Act
        $crawler = $this->client->request('GET', $this->controllerEndpoint."/{$id}/delete");
        $form = $crawler->selectButton('Ja, verwijder')->form();
        $crawler = $this->client->submit($form);

        $allGroups = $this->em->getRepository(Group::class)->findAll();

        // Assert
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        self::assertNotContains($group, $allGroups);
    }

    public function testRelationNewAction(): void
    {
        // Arrange
        $group = $this->em->getRepository(Group::class)->findAll()[0];
        $id = $group->getId();

        // Act
        $crawler = $this->client->request('GET', $this->controllerEndpoint."/relation/new/{$id}");
        $form = $crawler->selectButton('Toevoegen')->form();
        $crawler = $this->client->submit($form);
        $allGroups = $this->em->getRepository(Group::class)->findAll();
        $newGroupId = explode('group/', $crawler->getUri() ?? '')[1];
        $newGroup = $this->em->getRepository(Group::class)->find($newGroupId);

        // Assert
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        self::assertContains($newGroup, $allGroups);
    }

    public function testRelationAddAction(): void
    {
        // Arrange
        $relation = $this->em->getRepository(Relation::class)->findAll()[0];
        $id = $relation->getId();

        // Act
        $crawler = $this->client->request('GET', $this->controllerEndpoint."/relation/add/{$id}");
        $form = $crawler->selectButton('Toevoegen')->form();
        $form['relation_add[_token]'] = 'Penningmeester';
        $crawler = $this->client->submit($form);
        $allRelations = $this->em->getRepository(Relation::class)->findAll();
        $newGroupId = explode('add/', $crawler->getUri() ?? '')[1];
        $newRelation = $this->em->getRepository(Relation::class)->find($newGroupId);

        // Assert
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        self::assertContains($newRelation, $allRelations);
    }

    public function testRelationDeleteAction(): void
    {
        // Arrange
        $relation = $this->em->getRepository(Relation::class)->findAll()[0];
        $id = $relation->getId();

        // Act
        $crawler = $this->client->request('GET', $this->controllerEndpoint."/relation/delete/{$id}");
        $form = $crawler->selectButton('Ja, verwijder')->form();
        $crawler = $this->client->submit($form);

        $allRelations = $this->em->getRepository(Relation::class)->findAll();

        // Assert
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        self::assertNotContains($relation, $allRelations);
    }
}
