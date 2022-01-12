<?php

namespace Tests\Functional\Controller\Admin;

use App\Entity\Activity\Activity;
use App\Entity\Activity\Registration;
use App\Tests\AuthWebTestCase;
use App\Tests\Database\Activity\ActivityFixture;
use App\Tests\Database\Activity\PriceOptionFixture;
use App\Tests\Database\Activity\RegistrationFixture;
use App\Tests\Database\Security\LocalAccountFixture;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class RegistrationControllerTest.
 *
 * @covers \App\Controller\Admin\RegistrationController
 * @covers \App\Controller\Helper\RegistrationHelper
 *
 * @author A-Daneel
 */
class RegistrationControllerTest extends AuthWebTestCase
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $em;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->login();

        $this->loadFixtures([
            LocalAccountFixture::class,
            PriceOptionFixture::class,
            ActivityFixture::class,
            RegistrationFixture::class,
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

    public function testNewActionGet(): void
    {
        // Arrange
        $activity = $this->em->getRepository(Activity::class)->findAll()[0];
        $id = $activity->getId();

        // Act
        $this->client->request('GET', "/admin/activity/register/new/{$id}");

        // Assert
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    /**
     *  @depends testNewActionGet
     */
    public function testNewActionPost(): void
    {
        // Arrange
        $activity = $this->em->getRepository(Activity::class)->findAll()[0];
        $originalCount = $activity->getRegistrations()->count();
        $id = $activity->getId();

        // Act
        $this->client->request('GET', "/admin/activity/register/new/{$id}");
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
        /* @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testDeleteActionGet(): void
    {
        // Arrange
        $registration = $this->em->getRepository(Registration::class)->findAll()[0];
        $id = $registration->getId();

        // Act
        $this->client->request('GET', "/admin/activity/register/delete/{$id}");

        // Assert
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    /**
     *  @depends testDeleteActionGet
     */
    public function testDeleteActionPost(): void
    {
        // Arrange
        $registration = $this->em->getRepository(Registration::class)->findAll()[0];
        $id = $registration->getId();
        $this->assertEquals(null, $registration->getDeleteDate());

        // Act
        $this->client->request('GET', "/admin/activity/register/delete/{$id}");
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
        $activity = $this->em->getRepository(Activity::class)->findAll()[0];
        $id = $activity->getId();

        // Act
        $this->client->request('GET', "/admin/activity/register/reserve/new/{$id}");

        // Assert
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @depends testReserveNewActionGet
     */
    public function testReserveNewActionPost(): void
    {
        // Arrange
        $activity = $this->em->getRepository(Activity::class)->findAll()[0];
        $originalCount = $activity->getRegistrations()->count();
        $id = $activity->getId();

        // Act
        $this->client->request('GET', "/admin/activity/register/reserve/new/{$id}");
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
        $activity = $this->em->getRepository(Activity::class)->findAll()[0];
        $reserves = $this->em->getRepository(Registration::class)->findReserve($activity);
        $secondReserveId = $reserves[1]->getId();

        // Act
        $this->client->request('GET', "/admin/activity/register/reserve/move/{$secondReserveId}/up");

        // Assert
        $updatedReserves = $this->em->getRepository(Registration::class)->findReserve($activity);
        $updatedFirstReserveId = $updatedReserves[0]->getId();
        $this->assertEquals($updatedFirstReserveId, $secondReserveId);
        $this->assertSelectorTextContains('.container', 'naar boven verplaatst!');
    }

    public function testReserveMoveDownAction(): void
    {
        // Arrange
        $activity = $this->em->getRepository(Activity::class)->findAll()[0];
        $reserves = $this->em->getRepository(Registration::class)->findReserve($activity);
        $firstReserveId = $reserves[0]->getId();

        // Act
        $this->client->request('GET', "/admin/activity/register/reserve/move/{$firstReserveId}/down");

        // Assert
        $updatedReserves = $this->em->getRepository(Registration::class)->findReserve($activity);
        $updatedRegistrationId = $updatedReserves[1]->getId();
        $this->assertEquals($updatedRegistrationId, $firstReserveId);
        $this->assertSelectorTextContains('.container', 'naar beneden verplaatst!');
    }
}
