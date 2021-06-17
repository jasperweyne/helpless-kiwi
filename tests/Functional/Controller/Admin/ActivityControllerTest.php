<?php

namespace Tests\Functional\Controller\Admin;

use App\Controller\Admin\ActivityController;
use App\Log\EventService;
use App\Tests\AuthWebTestCase;
use App\Tests\Database\Activity\ActivityFixture;
use App\Tests\Database\Activity\PriceOptionFixture;
use App\Tests\Database\Activity\RegistrationFixture;
use App\Tests\Database\Security\LocalAccountFixture;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class ActivityControllerTest.
 *
 * @covers \App\Controller\Admin\ActivityController
 */
class ActivityControllerTest extends AuthWebTestCase
{
    /**
     * @var ActivityController
     */
    protected $activityController;

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

        unset($this->activityController);
        unset($this->events);
        unset($this->em);
    }

    public function testIndexAction(): void
    {
        $this->markTestIncomplete();
    }

    public function testNewAction(): void
    {
        $local_file = __DIR__.'/../../../assets/Faint.png';
        $activity_name = 'testname';

        // Act
        $crawler = $this->client->request('GET', '/admin/activity/new');

        // Act
        $form = $crawler->selectButton('Toevoegen')->form();
        $form['activity_new[name]'] = $activity_name;
        $form['activity_new[description]'] = 'added through testing';
        $form['activity_new[location][address]'] = 'In php unittest';
        $form['activity_new[deadline][date]'] = '2013-03-15';
        $form['activity_new[deadline][time]'] = '23:59';
        $form['activity_new[start][date]'] = '2013-03-15';
        $form['activity_new[start][time]'] = '23:59';
        $form['activity_new[end][date]'] = '2013-03-15';
        $form['activity_new[end][time]'] = '23:59';
        $form['activity_new[imageFile][file]'] = new UploadedFile(
            $local_file,
            'Faint.png',
            'image/png',
            null,
            null,
            true
        );
        $form['activity_new[color]'] = 1;

        $crawler = $this->client->submit($form);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('.container', 'Activiteit '.$activity_name);
    }

    public function testShowAction(): void
    {
        /* @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testEditAction(): void
    {
        /* @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testImageAction(): void
    {
        /* @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testDeleteAction(): void
    {
        /* @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testPriceNewAction(): void
    {
        /* @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testPriceEditAction(): void
    {
        /* @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testPresentEditAction(): void
    {
        /* @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testSetAmountPresent(): void
    {
        /* @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testResetAmountPresent(): void
    {
        /* @todo This test is incomplete. */
        $this->markTestIncomplete();
    }
}
