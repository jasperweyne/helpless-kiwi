<?php

namespace Tests\Functional\Controller\Activity;

use App\Entity\Activity\Activity;
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
        $activity = $this->em->getRepository(Activity::class)->findAll()[0];
        $id = $activity->getId();

        // Act
        $this->client->request('GET', "/activity/{$id}");

        // Assert
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testSingleUnregistrationForm(): void
    {
        /* @todo This test is incomplete. */
        self::markTestIncomplete();
    }

    public function testSingleRegistrationForm(): void
    {
        /* @todo This test is incomplete. */
        self::markTestIncomplete();
    }
}
