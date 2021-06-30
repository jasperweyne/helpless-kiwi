<?php

namespace Tests\Functional\Controller\Activity;

use App\Controller\Activity\ActivityController;
use App\Entity\Activity\Activity;
use App\Entity\Activity\PriceOption;
use App\Entity\Activity\Registration;
use App\Entity\Security\LocalAccount;
use App\Tests\AuthWebTestCase;
use App\Tests\Database\Activity\ActivityFixture;
use App\Tests\Database\Activity\PriceOptionFixture;
use App\Tests\Database\Activity\RegistrationFixture;
use App\Tests\Database\Security\LocalAccountFixture;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class ActivityControllerTest.
 *
 * @covers \App\Controller\Activity\ActivityController
 */
class ActivityControllerTest extends AuthWebTestCase
{
    /**
     * @var ActivityController
     */
    protected $activityController;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->login();

        /* @todo Correctly instantiate tested object to use it. */
        $this->activityController = new ActivityController();

        // Get all database tables
        $this->em = self::$container->get(EntityManagerInterface::class);
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
    }

    public function testIndexAction(): void
    {
        // Arrange
        $crawler = $this->client->request('GET', '/');
        $activities = $this->em->getRepository(Activity::class)->findAll(['hidden' => false], ['start' => 'DESC']);

        // Act
        $node = $crawler->filter('body > main > div.container > div.cardholder > div.grid-x')
            ->first()->filter('div.cell')
            ->first()->filter('h2');

        $exist = false;
        foreach ($activities as $activitie) {
            if ($activitie->getName() == $node->html() && true != $activitie->getHidden()) {
                $exist = true;
            }
        }

        // Assert
        $this->assertTrue($exist);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testUnregisterAction(): void
    {
        // Arrange
        /** @var LocalAccount */
        $user = $this->em->getRepository(LocalAccount::class)->findOneBy(['email' => LocalAccountFixture::USERNAME]);
        /** @var Registration */
        $reg = $this->em->getRepository(Registration::class)->findBy(['person' => $user])[0];
        $id = $reg->getActivity()->getId();

        // Act
        $this->client->request('GET', "/activity/{$id}");
        $this->client->submitForm('Afmelden');

        // Assert
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('.container', 'gelukt');
        /** @var Registration */
        $dereg = $this->em->getRepository(Registration::class)->find($reg->getId());
        $this->assertNotNull($dereg->getDeleteDate());
    }

    public function testRegisterAction(): void
    {
        // Arrange
        // Unload th Registration Fixture
        $this->loadFixtures([
            LocalAccountFixture::class,
            PriceOptionFixture::class,
            ActivityFixture::class,
        ]);

        // Retrieve data
        /** @var LocalAccount */
        $user = $this->em->getRepository(LocalAccount::class)->findOneBy(['email' => LocalAccountFixture::USERNAME]);
        /** @var PriceOption */
        $option = $this->em->getRepository(PriceOption::class)->findAll()[0];
        $id = $option->getActivity()->getId();

        // Act
        $this->client->request('GET', "/activity/{$id}");
        $this->client->submitForm('Aanmelden');

        // Assert
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('.container', 'gelukt');
        $reg = $this->em->getRepository(Registration::class)->findOneBy(['person' => $user, 'option' => $option]);
        $this->assertNotNull($reg);
    }

    public function testShowAction(): void
    {
        // Arrange
        $activity = $this->em->getRepository(Activity::class)->findAll()[0];
        $id = $activity->getId();

        // Act
        $this->client->request('GET', "/activity/{$id}");

        // Assert
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testSingleUnregistrationForm(): void
    {
        /* @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testSingleRegistrationForm(): void
    {
        /* @todo This test is incomplete. */
        $this->markTestIncomplete();
    }
}
