<?php

namespace Tests\Functional\Controller\Admin;

use App\Controller\Admin\ActivityController;
use App\Entity\Activity\Activity;
use App\Entity\Activity\PriceOption;
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
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $em;

    /**
     * @var string
     */
    private $controllerEndpoint = '/admin/activity';

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->loadFixtures([
            LocalAccountFixture::class,
            PriceOptionFixture::class,
            ActivityFixture::class,
            RegistrationFixture::class,
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

    public function testIndexAction(): void
    {
        $this->client->request('GET', $this->controllerEndpoint.'/');
        self::assertSelectorTextContains('span', 'Activiteiten');
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testIndexActionNotAdmin(): void
    {
        $this->logout();
        $this->login(false);
        $this->client->request('GET', $this->controllerEndpoint.'/');
        self::assertEquals(403, $this->client->getResponse()->getStatusCode());
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
            true
        );
        $form['activity_new[color]'] = '1';

        $crawler = $this->client->submit($form);
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        self::assertSelectorTextContains('.container', 'Activiteit '.$activity_name);
    }

    public function testShowAction(): void
    {
        /* Arrange */
        $activity = $this->em->getRepository(Activity::class)->findAll()[0];
        $id = $activity->getId();

        // Act
        $this->client->request('GET', $this->controllerEndpoint."/{$id}/");

        // Assert
        self::assertSelectorTextContains('span', "Activiteit {$activity->getName()}");
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testEditAction(): void
    {
        // Arrange
        $activity = $this->em->getRepository(Activity::class)->findAll()[0];
        $id = $activity->getId();
        $newName = 'Activity Editing!';

        // Act
        $crawler = $this->client->request('GET', $this->controllerEndpoint."/{$id}/edit");
        $form = $crawler->selectButton('Opslaan')->form();
        $form['activity_edit[name]'] = $newName;
        $crawler = $this->client->submit($form);
        $newActivity = $this->em->getRepository(Activity::class)->find($id);

        // Assert
        if (null == $newActivity) {
            self::fail();
        }
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        self::assertEquals($newName, $newActivity->getName());
    }

    public function testImageAction(): void
    {
        // Arrange
        $activity = $this->em->getRepository(Activity::class)->findAll()[0];
        $id = $activity->getId();
        $local_file = __DIR__.'/../../../assets/Faint.png';
        $newImage = new UploadedFile(
            $local_file,
            'Faint.png',
            'image/png',
            null,
            true
        );

        // Act
        $crawler = $this->client->request('GET', $this->controllerEndpoint."/{$id}/image");
        $form = $crawler->selectButton('Opslaan')->form();
        /** @var \Symfony\Component\DomCrawler\Field\FormField[] $form */
        $form['activity_image[imageFile][file]']->setValue($newImage);
        /** @var \Symfony\Component\DomCrawler\Form $form */
        $crawler = $this->client->submit($form);
        $newActivity = $this->em->getRepository(Activity::class)->find($id);

        // Assert
        if (null == $newActivity) {
            self::fail();
        }
        $activityImage = $newActivity->getImage();

        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        self::assertNotNull($activityImage);
        self::assertEquals($newImage->getClientOriginalName(), $activityImage->getName());
    }

    public function testDeleteAction(): void
    {
        // Arrange
        $activity = $this->em->getRepository(Activity::class)->findAll()[0];
        $id = $activity->getId();

        // Act
        $crawler = $this->client->request('GET', $this->controllerEndpoint."/{$id}/delete");
        $form = $crawler->selectButton('Ja, verwijder')->form();
        $crawler = $this->client->submit($form);

        $allActivity = $this->em->getRepository(Activity::class)->findAll();

        // Assert
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        self::assertNotContains($activity, $allActivity);
    }

    public function testPriceNewAction(): void
    {
        // Arrange
        $activity = $this->em->getRepository(Activity::class)->findAll()[0];
        $id = $activity->getId();
        $priceName = 'Free';
        $priceAmount = 1;
        $priceOptions = count($activity->getOptions());

        //Act
        $crawler = $this->client->request('GET', $this->controllerEndpoint."/price/new/{$id}");
        $form = $crawler->selectButton('Toevoegen')->form();
        /** @var \Symfony\Component\DomCrawler\Field\FormField[] $form */
        $form['price_option[name]']->setValue($priceName);
        $form['price_option[price]']->setValue((string) $priceAmount);
        /** @var \Symfony\Component\DomCrawler\Form $form */
        $crawler = $this->client->submit($form);

        $editedActivity = $this->em->getRepository(Activity::class)->find($id);
        self::assertInstanceOf(Activity::class, $editedActivity);
        $newPriceOptions = count($editedActivity->getOptions());

        // Assert
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        self::assertTrue($newPriceOptions > $priceOptions);
    }

    public function testPriceEditAction(): void
    {
        //Arrange
        /** @var Activity $activity */
        $activity = $this->em->getRepository(Activity::class)->findAll()[0];
        $priceOption = $activity->getOptions()[0];
        self::assertInstanceOf(PriceOption::class, $priceOption);
        $id = $priceOption->getId();
        $newPrice = 0;

        //Act
        $crawler = $this->client->request('GET', $this->controllerEndpoint."/price/{$id}");
        $form = $crawler->selectButton('Opslaan')->form();
        /** @var \Symfony\Component\DomCrawler\Field\FormField[] $form */
        $form['price_option[price]']->setValue((string) $newPrice);
        /** @var \Symfony\Component\DomCrawler\Form $form */
        $crawler = $this->client->submit($form);
        $newPriceOption = $this->em->getRepository(PriceOption::class)->find($id);

        // Assert
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        self::assertInstanceOf(PriceOption::class, $newPriceOption);
        self::assertEquals($newPrice, $newPriceOption->getPrice());
    }

    public function testPresentEditAction(): void
    {
        //Arange
        $activities = $this->em->getRepository(Activity::class)->findAll();
        $id = $activities[0]->getId();

        //Act
        $crawler = $this->client->request('GET', "/admin/activity/{$id}/present");
        $form = $crawler->selectButton('Opslaan')->form();
        /** @var \Symfony\Component\DomCrawler\Field\FormField[] $form */
        $form['activity_edit_present[registrations][0][present]']->setValue('2');
        /** @var \Symfony\Component\DomCrawler\Form $form */
        $this->client->submit($form);

        //Assert
        self::assertSelectorTextContains('.flash', 'Aanwezigheid aangepast');
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testSetAmountPresent(): void
    {
        //Arange
        $activitie = $this->em->getRepository(Activity::class)->findAll()[0];
        $id = $activitie->getId();
        $present = $activitie->getPresent();

        //Act
        $crawler = $this->client->request('GET', "/admin/activity/{$id}/setamountpresent");
        $form = $crawler->selectButton('Opslaan')->form();
        $form['activity_set_present_amount[present]'] = '1000';
        $this->client->submit($form);
        $newActivity = $this->em->getRepository(Activity::class)->find($id);

        //Assert
        if (null == $newActivity) {
            self::fail();
        }
        self::assertSelectorTextContains('.flash', 'Aanwezigen genoteerd!');
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        self::assertTrue($newActivity->getPresent() != $present);
    }

    public function testResetAmountPresent(): void
    {
        //Arange
        $activitie = $this->em->getRepository(Activity::class)->findAll()[0];
        $id = $activitie->getId();

        //Act
        $crawler = $this->client->request('GET', "/admin/activity/{$id}/resetamountpresent");
        $form = $crawler->selectButton('Opslaan')->form();
        $this->client->submit($form);
        $newActivity = $this->em->getRepository(Activity::class)->find($id);

        //Assert
        if (null == $newActivity) {
            self::fail();
        }
        self::assertSelectorTextContains('.flash', 'Aanwezigen geteld!');
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        self::assertTrue(null == $newActivity->getPresent());
    }
}
