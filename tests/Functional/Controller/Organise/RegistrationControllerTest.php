<?php

namespace Tests\Functional\Controller\Organise;

use App\Entity\Activity\Activity;
use App\Entity\Activity\Registration;
use App\Entity\Group\Relation;
use App\Entity\Security\LocalAccount;
use App\Tests\AuthWebTestCase;
use App\Tests\Database\Activity\ActivityFixture;
use App\Tests\Database\Activity\RegistrationFixture;
use App\Tests\Database\Group\GroupFixture;
use App\Tests\Database\Group\RelationFixture;
use App\Tests\Database\Security\LocalAccountFixture;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class RegistrationControllerTest.
 *
 * @covers \App\Controller\Organise\RegistrationController
 * @covers \App\Controller\Helper\RegistrationHelper
 *
 * @author A-Daneel
 */
class RegistrationControllerTest extends AuthWebTestCase
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->login();

        $this->loadFixtures([
            LocalAccountFixture::class,
            ActivityFixture::class,
            RegistrationFixture::class,
            GroupFixture::class,
            RelationFixture::class,
        ]);

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

    public function testNewActionUnauthorized(): void
    {
        // Arrange
        $this->loadFixtures([
            LocalAccountFixture::class,
            ActivityFixture::class,
            RegistrationFixture::class,
            GroupFixture::class,
        ]);
        $activity = $this->em->getRepository(Activity::class)->findAll()[0];
        $id = $activity->getId();

        // Act
        $this->client->request('GET', "/organise/activity/register/new/{$id}");

        // Assert
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testNewActionGet(): void
    {
        // Arrange
        $user = $this->em->getRepository(LocalAccount::class)->findBy(['email' => LocalAccountFixture::USERNAME])[0];
        $relation = $this->em->getRepository(Relation::class)->findBy(['person' => $user])[0];
        $activity = $this->em->getRepository(Activity::class)->findBy(['author' => $relation->getGroup()->getId()])[0];
        $id = $activity->getId();

        // Act
        $this->client->request('GET', "/organise/activity/register/new/{$id}");

        // Assert
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    /**
     *  @depends testNewActionGet
     */
    public function testNewActionPost(): void
    {
        // Arrange
        $user = $this->em->getRepository(LocalAccount::class)->findBy(['email' => LocalAccountFixture::USERNAME])[0];
        $relation = $this->em->getRepository(Relation::class)->findBy(['person' => $user])[0];
        $activity = $this->em->getRepository(Activity::class)->findBy(['author' => $relation->getGroup()->getId()])[0];
        $originalCount = $activity->getRegistrations()->count();
        $id = $activity->getId();

        // Act
        $this->client->request('GET', "/organise/activity/register/new/{$id}");
        $this->client->submitForm('Toevoegen');

        // Assert
        $activity = $this->em->getRepository(Activity::class)->find($id);
        $newCount = $activity->getRegistrations()->count();
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('.container', 'aangemeld');
        $this->assertEquals(1, $newCount - $originalCount, "Registration count of activity didn't correctly change after POST request.");
    }

    public function testEditActionPost(): void
    {
        // Arrange
        $registration = $this->em->getRepository(Registration::class)->findAll()[0];
        $id = $registration->getId();
        $crawler = $this->client->request('GET', "/organise/activity/register/edit/{$id}");
        $comment = 'This is a test comment';

        // Act
        $form = $crawler->selectButton('Verander')->form();
        $form['registration_edit[comment]'] = $comment;
        $this->client->submit($form);

        // Assert
        $currentcomment = $this->em->getRepository(Registration::class)->find($id);
        $newcomment = $currentcomment->getComment();

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertEquals($comment, $newcomment);
    }

    public function testDeleteActionGet(): void
    {
        // Arrange
        $user = $this->em->getRepository(LocalAccount::class)->findBy(['email' => LocalAccountFixture::USERNAME])[0];
        $relation = $this->em->getRepository(Relation::class)->findBy(['person' => $user])[0];
        $activity = $this->em->getRepository(Activity::class)->findBy(['author' => $relation->getGroup()->getId()])[0];
        $registration = $activity->getRegistrations()[0];
        $id = $registration->getId();

        // Act
        $this->client->request('GET', "/organise/activity/register/delete/{$id}");

        // Assert
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    /**
     *  @depends testDeleteActionGet
     */
    public function testDeleteActionPost(): void
    {
        // Arrange
        $user = $this->em->getRepository(LocalAccount::class)->findBy(['email' => LocalAccountFixture::USERNAME])[0];
        $relation = $this->em->getRepository(Relation::class)->findBy(['person' => $user])[0];
        $activity = $this->em->getRepository(Activity::class)->findBy(['author' => $relation->getGroup()->getId()])[0];
        $registration = $activity->getRegistrations()[0];
        $id = $registration->getId();

        // Act
        $this->client->request('GET', "/organise/activity/register/delete/{$id}");
        $this->client->submitForm('Ja, meld af');

        // Assert
        $registration = $this->em->getRepository(Registration::class)->find($id);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('.container', 'afgemeld');
        $this->assertNotNull($registration->getDeleteDate());
    }

    public function testReserveNewActionGet(): void
    {
        // Arrange
        $user = $this->em->getRepository(LocalAccount::class)->findBy(['email' => LocalAccountFixture::USERNAME])[0];
        $relation = $this->em->getRepository(Relation::class)->findBy(['person' => $user])[0];
        $activity = $this->em->getRepository(Activity::class)->findBy(['author' => $relation->getGroup()->getId()])[0];
        $id = $activity->getId();

        // Act
        $this->client->request('GET', "/organise/activity/register/reserve/new/{$id}");

        // Assert
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @depends testReserveNewActionGet
     */
    public function testReserveNewActionPost(): void
    {
        // Arrange
        $user = $this->em->getRepository(LocalAccount::class)->findBy(['email' => LocalAccountFixture::USERNAME])[0];
        $relation = $this->em->getRepository(Relation::class)->findBy(['person' => $user])[0];
        $activity = $this->em->getRepository(Activity::class)->findBy(['author' => $relation->getGroup()->getId()])[0];
        $originalCount = $activity->getRegistrations()->count();
        $id = $activity->getId();

        // Act
        $this->client->request('GET', "/organise/activity/register/reserve/new/{$id}");
        $this->client->submitForm('Toevoegen');

        // Assert
        $activity = $this->em->getRepository(Activity::class)->find($id);
        $newCount = $activity->getRegistrations()->count();
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(1, $newCount - $originalCount, "Registration count of activity didn't correctly change after POST request.");
        $this->assertSelectorTextContains('.container', 'aangemeld op de reservelijst');
    }

    public function testReserveMoveUpAction(): void
    {
        // Arrange
        $user = $this->em->getRepository(LocalAccount::class)->findBy(['email' => LocalAccountFixture::USERNAME])[0];
        $relation = $this->em->getRepository(Relation::class)->findBy(['person' => $user])[0];
        $activity = $this->em->getRepository(Activity::class)->findBy(['author' => $relation->getGroup()->getId()])[0];
        $reserves = $this->em->getRepository(Registration::class)->findReserve($activity);
        $secondReserveId = $reserves[1]->getId();

        // Act
        $this->client->request('GET', "/organise/activity/register/reserve/move/{$secondReserveId}/up");

        // Assert
        $updatedReserves = $this->em->getRepository(Registration::class)->findReserve($activity);
        $updatedFirstReserveId = $updatedReserves[0]->getId();
        $this->assertEquals($updatedFirstReserveId, $secondReserveId);
        $this->assertSelectorTextContains('.container', 'naar boven verplaatst!');
    }

    public function testReserveMoveDownAction(): void
    {
        // Arrange
        $user = $this->em->getRepository(LocalAccount::class)->findBy(['email' => LocalAccountFixture::USERNAME])[0];
        $relation = $this->em->getRepository(Relation::class)->findBy(['person' => $user])[0];
        $activity = $this->em->getRepository(Activity::class)->findBy(['author' => $relation->getGroup()->getId()])[0];
        $reserves = $this->em->getRepository(Registration::class)->findReserve($activity);
        $firstReserveId = $reserves[0]->getId();

        // Act
        $this->client->request('GET', "/organise/activity/register/reserve/move/{$firstReserveId}/down");

        // Assert
        $updatedReserves = $this->em->getRepository(Registration::class)->findReserve($activity);
        $updatedRegistrationId = $updatedReserves[1]->getId();
        $this->assertEquals($updatedRegistrationId, $firstReserveId);
        $this->assertSelectorTextContains('.container', 'naar beneden verplaatst!');
    }
}
