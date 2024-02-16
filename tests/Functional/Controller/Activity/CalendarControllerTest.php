<?php

namespace Tests\Functional\Controller\Activity;

use App\Entity\Security\LocalAccount;
use App\Tests\AuthWebTestCase;
use App\Tests\Database\Security\LocalAccountFixture;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class CalendarControllerTest.
 *
 * @covers \App\Controller\Activity\CalendarController
 */
class CalendarControllerTest extends AuthWebTestCase
{
    protected EntityManagerInterface $em;
    protected ReferenceRepository $fixtures;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Get all database tables
        $this->fixtures = $this->databaseTool->loadFixtures([
            LocalAccountFixture::class,
        ])->getReferenceRepository();

        $this->login();
        $this->em = self::getContainer()->get(EntityManagerInterface::class);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();
    }

    public function testGetCalendar(): void
    {
        $this->client->request('GET', '/ical');
        $icalReturnPrefix = 'BEGIN:VCALENDAR';
        $icalReturnSuffix = "END:VCALENDAR\r\n";
        $icalResponse = $this->client->getResponse();

        self::assertNotFalse($icalResponse->getContent());
        self::assertStringStartsWith($icalReturnPrefix, $icalResponse->getContent());
        self::assertStringEndsWith($icalReturnSuffix, $icalResponse->getContent());
        self::assertSame(200, $icalResponse->getStatusCode());
    }

    public function testGetPersonalCalendar(): void
    {
        /** @var LocalAccount $user */
        $user = $this->fixtures->getReference(LocalAccountFixture::LOCAL_ACCOUNT_REFERENCE);
        $token = $user->getCalendarToken();

        $this->client->request('GET', '/ical/personal/'.$token);
        $icalResponse = $this->client->getResponse();

        self::assertSame(200, $icalResponse->getStatusCode());
    }

    public function testPostPersonalCalendarRenew(): void
    {
        /** @var LocalAccount $user */
        $user = $this->fixtures->getReference(LocalAccountFixture::LOCAL_ACCOUNT_REFERENCE);
        $token = $user->getCalendarToken();

        $this->client->request('POST', '/ical/renew');

        self::assertNotSame($user->getCalendarToken(), $token);
    }
}
