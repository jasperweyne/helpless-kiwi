<?php

namespace Tests\Functional\Controller\Activity;

use App\Entity\Activity\Activity;
use App\Entity\Activity\PriceOption;
use App\Entity\Activity\WaitlistSpot;
use App\Tests\AuthWebTestCase;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class ActivityControllerTest.
 *
 * @covers \App\Controller\Activity\ActivityController
 */
class ActivityControllerTest extends AuthWebTestCase
{
    protected EntityManagerInterface $em;

    protected function setUp(): void
    {
        parent::setUp();

        $this->login();
        $this->em = self::getContainer()->get(EntityManagerInterface::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    public function testIndexAction(): void
    {
        // Arrange
        $crawler = $this->client->request('GET', '/');
        $activities = $this->em->getRepository(Activity::class)->findAll();

        // Act
        $node = $crawler->filter('main .grid a h2')->first();

        $exist = false;
        /** @var Activity $activity */
        foreach ($activities as $activity) {
            if ($activity->getName() == $node->html() && null !== $activity->getVisibleAfter() && $activity->getVisibleAfter() < new \DateTime()) {
                $exist = true;
            }
        }

        // Assert
        self::assertTrue($exist);
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testShowAction(): void
    {
        // Arrange
        $activity = $this->em->getRepository(Activity::class)->findOneBy(['name' => 'Activity_2']);
        self::assertNotNull($activity);
        $activityId = $activity->getId();

        // Act
        $this->client->request('GET', "/activity/{$activityId}");

        // Assert
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testShowActionEngage(): void
    {
        // Arrange
        $activity = $this->em->getRepository(Activity::class)->findOneBy(['name' => 'Activity_2']);
        self::assertNotNull($activity);
        $activityId = $activity->getId();

        // Act
        $crawler = $this->client->request('GET', "/activity/{$activityId}");
        $form = $crawler->selectButton('Aanmelden')->form();
        $crawler = $this->client->submit($form);

        // Assert
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        self::assertSelectorExists('.flash', 'Aanmelding gelukt!');
    }

    public function testShowActionDisengageWaitlist(): void
    {
        // Arrange
        $activity = $this->em->getRepository(Activity::class)->findOneBy(['name' => 'Activity_2']);
        self::assertNotNull($activity);
        $options = $activity->getOptions();
        self::assertGreaterThan(0, $options->count());
        /** @var PriceOption $option */
        $option = $options->first();
        $activityId = $activity->getId();

        /** @var ?\App\Entity\Security\LocalAccount $user */
        $user = $this->em->getRepository(\App\Entity\Security\LocalAccount::class)->findOneBy(['email' => 'admin@kiwi.nl']);
        self::assertNotNull($user);

        $waitlistSpot = new WaitlistSpot($user, $option);
        $this->em->persist($waitlistSpot);
        $this->em->flush();

        // Act
        $crawler = $this->client->request('GET', "/activity/{$activityId}");
        $form = $crawler->selectButton('Afmelden wachtlijst')->form();
        $crawler = $this->client->submit($form);

        // Assert
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());

        $this->em->clear();
        $waitlist = $this->em->getRepository(WaitlistSpot::class)->findOneBy([
            'person' => $user,
            'option' => $option,
        ]);
        self::assertNull($waitlist);
    }
}
