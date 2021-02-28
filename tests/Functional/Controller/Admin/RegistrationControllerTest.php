<?php

namespace Tests\Functional\Controller\Admin;

use App\DataFixtures\Activity\ActivityFixture;
use App\DataFixtures\Activity\PriceOptionFixture;
use App\DataFixtures\Security\LocalAccountFixture;
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
        $this->client->followRedirects(true);

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

    public function testNewAction(): void
    {
        $activity = $this->em->getRepository(Activity::class)->findAll()[0];

        $originalCount = $activity->getOptions()->count();

        $crawler = $this->client->request('GET', '/admin/activity/register/new/'.$activity->getId());
        $form = $crawler->selectButton('Toevoegen')->form();
        $crawler = $this->client->submit($form);

        $newCount = $activity->getOptions()->count();

        var_dump($crawler->filter('.messages')->extract(['_text']));

        // $this->assertEquals(1, $newCount - $originalCount);
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
