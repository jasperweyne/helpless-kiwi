<?php

namespace Tests\Functional\Controller\Activity;

use App\Entity\Activity\Activity;
use App\Entity\Activity\PriceOption;
use App\Entity\Activity\Registration;
use App\Entity\Security\LocalAccount;
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

    public function testUnregisterAction(): void
    {
        // Arrange
        /** @var LocalAccount */
        $user = $this->em->getRepository(LocalAccount::class)->findOneBy(['email' => 'aangemeld@kiwi.nl']);
        $this->login('aangemeld@kiwi.nl');

        /** @var Registration */
        $reg = $this->em->getRepository(Registration::class)->findOneBy(['person' => $user]);
        self::assertNotNull($reg->getActivity());
        $id = $reg->getActivity()->getId();

        // Act
        $this->client->request('GET', "/activity/{$id}/");
        $this->client->submitForm('Afmelden');

        // Assert
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        self::assertSelectorTextContains('.container', 'gelukt');
        /** @var Registration */
        $dereg = $this->em->getRepository(Registration::class)->findOneBy(['person' => $user]);
        self::assertNotNull($dereg->getDeleteDate());
    }

    public function testRegisterAction(): void
    {
        // Retrieve data
        /** @var LocalAccount */
        $user = $this->em->getRepository(LocalAccount::class)->findOneBy(['email' => 'afgemeld@kiwi.nl']);
        /** @var PriceOption */
        $option = $this->em->getRepository(PriceOption::class)->findOneBy(['name' => 'reg_test']);
        self::assertNotNull($option->getActivity());
        $id = $option->getActivity()->getId();
        self::assertNotNull($id);
        $this->em->clear();

        // Act
        $crawler = $this->client->request('GET', "/activity/{$id}/");
        $filter = "input[value='{$option->getId()}']";
        $form = $crawler->filter($filter)->ancestors()->filter('form')->form();
        $this->client->submit($form);

        // Assert
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        self::assertSelectorTextContains('.container', 'gelukt');
        $reg = $this->em->getRepository(Registration::class)->findOneBy(['person' => $user, 'option' => $option]);
        self::assertNotNull($reg);
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
