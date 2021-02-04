<?php

namespace Tests\Functional\Controller\Admin;

use App\Controller\Admin\RegistrationController;
use App\DataFixtures\Activity\ActivityFixture;
use App\DataFixtures\Activity\PriceOptionFixture;
use App\DataFixtures\Location\LocationFixture;
use App\DataFixtures\Security\LocalAccountFixture;
use App\Tests\Helper\AuthWebTestCase;

/**
 * Class RegistrationControllerTest.
 *
 * @covers \App\Controller\Admin\RegistrationController
 */
class RegistrationControllerTest extends AuthWebTestCase
{
    /**
     * @var RegistrationController
     */
    protected $registrationController;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        /* @todo Correctly instantiate tested object to use it. */
        $this->registrationController = new RegistrationController();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->registrationController);
    }

    public function testNewAction(): void
    {
//        $fixtures = $this->loadFixtures([
//            LocalAccountFixture::class,
//            LocationFixture::class,
//            PriceOptionFixture::class,
//            ActivityFixture::class,
//        ])->getReferenceRepository();
//
//        $this->login();
//
//        //$crawler = $this->client->request('GET', '/admin/activity/'.$fixtures->getReference('Activity 0')->getId());
//        $crawler = $this->client->request('GET', '/');
//        $this->assertResponseIsSuccessful();
//        $link = $crawler
//            ->filter('a[class^="activity"]') // find all links with the text "Greet"
//            ->eq(0) // select the second link in the list
//            ->link()
//        ;
        //$this->assertGreaterThan(0, $crawler->filter('//button[@class="button add icon"]'));
        $this->markTestIncomplete();
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
