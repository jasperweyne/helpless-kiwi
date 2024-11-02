<?php

namespace Tests\Functional\Controller\Activity;

use App\Entity\Security\LocalAccount;
use App\Tests\AuthWebTestCase;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class CalendarControllerTest.
 *
 * @covers \App\Controller\Activity\CalendarController
 */
class CalendarControllerTest extends AuthWebTestCase
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
        $user = $this->em->getRepository(LocalAccount::class)->findOneBy(['email' => 'admin@kiwi.nl']);
        $token = $user->getCalendarToken();

        $this->client->request('GET', '/ical/personal/'.$token);
        $icalResponse = $this->client->getResponse();

        self::assertSame(200, $icalResponse->getStatusCode());
    }

    public function testPostPersonalCalendarRenew(): void
    {
        /** @var LocalAccount $user */
        $user = $this->em->getRepository(LocalAccount::class)->findOneBy(['email' => 'admin@kiwi.nl']);
        $token = $user->getCalendarToken();

        $this->client->request('POST', '/ical/renew');

        self::assertNotSame($user->getCalendarToken(), $token);
    }
}
