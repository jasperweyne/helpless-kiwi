<?php

namespace Tests\Functional\Controller\Admin;

use Tests\Helper\Database\Activity\ActivityFixture;
use Tests\Helper\Database\Activity\PriceOptionFixture;
use Tests\Helper\Database\Security\LocalAccountFixture;
use App\Entity\Activity\Activity;
use Doctrine\ORM\EntityManagerInterface;
use Tests\Helper\AuthWebTestCase;

/**
 * Class RegistrationControllerTest.
 *
 * @covers \App\Controller\Admin\RegistrationController
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

    public function testNewGetAction(): void
    {
        // Arrange
        $activity = $this->em->getRepository(Activity::class)->findAll()[0];
        $id = $activity->getId();

        // Act
        $this->client->request('GET', "/admin/activity/register/new/{$id}");

        // Assert
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }
    
    public function testNewPostAction(): void
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
        $this->assertEquals(1, $newCount - $originalCount, "Registration count of activity didn't correctly change after POST request.");
        // $this->assertSelectorTextContains('.messages', 'aangemeld');
    }

    public function testDeleteAction(): void
    {
        /* @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testReserveNewAction(): void
    {
        /* @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testReserveMoveUpAction(): void
    {
        /* @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testReserveMoveDownAction(): void
    {
        /* @todo This test is incomplete. */
        $this->markTestIncomplete();
    }
}
