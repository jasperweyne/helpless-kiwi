<?php

namespace Tests\Unit\Calendar;

use App\Calendar\CalendarProvider;
use App\Entity\Activity\Activity;
use App\Entity\Location\Location;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @author A-Daneel
 *
 * @covers \App\Calendar\CalendarProvider
 */
class CalendarProviderTest extends KernelTestCase
{
    protected CalendarProvider $calendarProvider;

    protected Activity $firstActivity;
    protected Activity $secondActivity;
    protected Activity $invalidActivity;

    protected \DateTime $now;

    protected string $summary;
    protected string $description;

    protected Location $location;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->calendarProvider = new CalendarProvider();
        $this->firstActivity = new Activity();
        $this->secondActivity = new Activity();
        $this->invalidActivity = new Activity();

        $this->now = new \DateTime();
        $this->summary = 'kiwi test summary';
        $this->description = 'kiwi test description';
        $this->location = new Location();

        $this->location->setAddress('@localhost');

        $this
            ->firstActivity
            ->setStart($this->now)
            ->setEnd($this->now)
            ->setName($this->summary)
            ->setLocation($this->location)
            ->setDescription($this->description)
            ->setId('a')
        ;

        $this
            ->secondActivity
            ->setStart($this->now)
            ->setEnd($this->now)
            ->setName('second '.$this->summary)
            ->setLocation($this->location)
            ->setDescription('second '.$this->description)
            ->setId('b')
        ;

        $this
            ->invalidActivity
            ->setStart($this->now)
            ->setEnd($this->now)
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->calendarProvider);
        unset($this->firstActivity);
        unset($this->secondActivity);
        unset($this->invalidActivity);
    }

    /**
     * @test
     */
    public function calendarItemSuccesWithSingleEvent(): void
    {
        $received = $this->calendarProvider->calendarItem(
            $this->firstActivity
        );

        self::assertStringContainsString('PRODID:-//Helpless Kiwi//'.$_ENV['ORG_NAME'].' v1.0//NL', $received);
        $start = $this->firstActivity->getStart();
        if (!is_null($start)) {
            $start = $start->format('Ymd\THis');
        }
        self::assertStringContainsString('DTSTART:'.$start, $received);
        self::assertStringContainsString('SUMMARY:'.$this->summary, $received);
        self::assertStringContainsString('LOCATION:'.$this->location->getAddress(), $received);
        self::assertStringContainsString('DESCRIPTION:kiwi test description', $received);
    }

    /**
     * @test
     */
    public function calendarItemSuccesWithErrorHandling(): void
    {
        $this->expectError();
        $this->calendarProvider->calendarItem(
            $this->invalidActivity
        );
    }

    /**
     * @test
     */
    public function calendarFeedeedSuccesWithSingleEvent(): void
    {
        $received = $this->calendarProvider->calendarFeed([
            $this->firstActivity,
        ]);
        self::assertStringContainsString('PRODID:-//Helpless Kiwi//'.$_ENV['ORG_NAME'].' v1.0//NL', $received);
        $start = $this->firstActivity->getStart();
        if (!is_null($start)) {
            $start = $start->format('Ymd\THis');
        }
        self::assertStringContainsString('DTSTART:'.$start, $received);
        self::assertStringContainsString('SUMMARY:'.$this->summary, $received);
        self::assertStringContainsString('LOCATION:'.$this->location->getAddress(), $received);
        self::assertStringContainsString('DESCRIPTION:'.$this->description, $received);
    }

    /**
     * @test
     */
    public function calendarFeedSuccesWithTwoValidEvents(): void
    {
        $received = $this->calendarProvider->calendarFeed([
            $this->firstActivity,
            $this->secondActivity,
        ]);
        self::assertStringContainsString('SUMMARY:'.$this->summary, $received);
        self::assertStringContainsString('SUMMARY:second '.$this->summary, $received);
    }

    /**
     * @test
     */
    public function calendarFeedSuccesWithOneValidAndOneInvalidEvent(): void
    {
        $received = $this->calendarProvider->calendarFeed([
            $this->invalidActivity,
            $this->secondActivity,
        ]);
        self::assertStringContainsString('SUMMARY:second '.$this->summary, $received);
    }

    /**
     * @test
     */
    public function calendarFeedSuccesWithMissingEnvVariable(): void
    {
        $_orgName = $_ENV['ORG_NAME'];
        unset($_ENV['ORG_NAME']);
        $received = $this->calendarProvider->calendarFeed([
            $this->firstActivity,
        ]);
        self::assertStringContainsString('PRODID:-//Helpless Kiwi//kiwi v1.0//NL', $received);
        $_ENV['ORG_NAME'] = $_orgName;
    }
}
