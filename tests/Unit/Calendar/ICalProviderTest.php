<?php

namespace Tests\Unit\Calendar;

use App\Calendar\ICalProvider;
use App\Entity\Activity\Activity;
use App\Entity\Location\Location;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class ICalProviderTest.
 *
 * @author A-Daneel
 * @covers \App\Calendar\ICalProvider
 */
class ICalProviderTest extends KernelTestCase
{
    /**
     * @var ICalProvider
     */
    protected $iCalProvider;

    /**
     * @var activity
     */
    protected $firstActivity;
    protected $secondActivity;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->iCalProvider = new ICalProvider();
        $this->firstActivity = new Activity();
        $this->secondActivity = new Activity();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->iCalProvider);
        unset($this->firstActivity);
        unset($this->secondActivity);
    }

    /**
     * @test
     */
    public function icalsingleSuccesWithSingleEvent(): void
    {
        $now = new \DateTime();
        $summary = 'kiwi test summary';
        $description = 'kiwi test description';
        $location = new Location();
        $location->setAddress('@localhost');

        $this
            ->firstActivity
            ->setStart($now)
            ->setEnd($now)
            ->setName($summary)
            ->setLocation($location)
            ->setDescription($description)
        ;

        $recieved = $this->iCalProvider->icalSingle(
            $this->firstActivity
        )->export();
        $this->assertStringContainsString('PRODID:-//Helpless Kiwi//'.$_ENV['ORG_NAME'].' v1.0//NL', $recieved);
        $this->assertStringContainsString('DTSTART:'.$this->firstActivity->getStart()->format('Ymd\THis'), $recieved);
        $this->assertStringContainsString('SUMMARY:kiwi test summary', $recieved);
        $this->assertStringContainsString('LOCATION:@localhost', $recieved);
        $this->assertStringContainsString('DESCRIPTION:kiwi test description', $recieved);
    }

    /**
     * @test
     */
    public function icalsingleSuccesWithErrorHandling(): void
    {
        $now = new \DateTime();
        $location = new Location();
        $location->setAddress('@localhost');

        $this
            ->firstActivity
            ->setStart($now)
            ->setEnd($now)
        ;

        $this->expectExceptionMessage('Error: Failed to create the event');
        $this->iCalProvider->icalSingle(
            $this->firstActivity
        )->export();
    }

    /**
     * @test
     */
    public function icalfeedSuccesWithSingleEvent(): void
    {
        $now = new \DateTime();
        $summary = 'kiwi test summary';
        $description = 'kiwi test description';
        $location = new Location();
        $location->setAddress('@localhost');

        $this
            ->firstActivity
            ->setStart($now)
            ->setEnd($now)
            ->setName($summary)
            ->setLocation($location)
            ->setDescription($description)
        ;

        $recieved = $this->iCalProvider->icalFeed([
            $this->firstActivity,
        ])->export();
        $this->assertStringContainsString('PRODID:-//Helpless Kiwi//'.$_ENV['ORG_NAME'].' v1.0//NL', $recieved);
        $this->assertStringContainsString('DTSTART:'.$this->firstActivity->getStart()->format('Ymd\THis'), $recieved);
        $this->assertStringContainsString('SUMMARY:kiwi test summary', $recieved);
        $this->assertStringContainsString('LOCATION:@localhost', $recieved);
        $this->assertStringContainsString('DESCRIPTION:kiwi test description', $recieved);
    }

    /**
     * @test
     */
    public function icalfeedSuccesWithTwoValidEvents(): void
    {
        $now = new \DateTime();
        $summary = 'kiwi test summary';
        $description = 'kiwi test description';
        $location = new Location();
        $location->setAddress('@localhost');

        $this
            ->firstActivity
            ->setStart($now)
            ->setEnd($now)
            ->setName($summary)
            ->setLocation($location)
            ->setDescription($description)
        ;

        $this
            ->secondActivity
            ->setStart($now)
            ->setEnd($now)
            ->setName('second '.$summary)
            ->setLocation($location)
            ->setDescription('second '.$description)
        ;

        $recieved = $this->iCalProvider->icalFeed([
            $this->firstActivity,
            $this->secondActivity,
        ])->export();
        $this->assertStringContainsString('SUMMARY:kiwi test summary', $recieved);
        $this->assertStringContainsString('SUMMARY:second kiwi test summary', $recieved);
    }

    /**
     * @test
     */
    public function icalfeedSuccesWithOneValidAndOneInvalidEvent(): void
    {
        $now = new \DateTime();
        $summary = 'kiwi test summary';
        $description = 'kiwi test description';
        $location = new Location();
        $location->setAddress('@localhost');

        $this
            ->firstActivity
            ->setName($summary)
            ->setLocation($location)
            ->setDescription($description)
        ;

        $this
            ->secondActivity
            ->setStart($now)
            ->setEnd($now)
            ->setName('second '.$summary)
            ->setLocation($location)
            ->setDescription('second '.$description)
        ;

        $recieved = $this->iCalProvider->icalFeed([
            $this->firstActivity,
            $this->secondActivity,
        ])->export();
        $this->assertStringContainsString('SUMMARY:second kiwi test summary', $recieved);
    }

    /**
     * @test
     */
    public function icalfeedSuccesWithMissingEnvVariable(): void
    {
        $_orgName = $_ENV['ORG_NAME'];
        $_tempOrgName = 'cuppcake';
        $_ENV['ORG_NAME'] = $_tempOrgName;
        $now = new \DateTime();
        $summary = 'kiwi test summary';
        $description = 'kiwi test description';
        $location = new Location();
        $location->setAddress('@localhost');

        $this
            ->firstActivity
            ->setStart($now)
            ->setEnd($now)
            ->setName($summary)
            ->setLocation($location)
            ->setDescription($description)
        ;

        $recieved = $this->iCalProvider->icalFeed([
            $this->firstActivity,
        ])->export();
        $this->assertStringContainsString('PRODID:-//Helpless Kiwi//'.$_ENV['ORG_NAME'].' v1.0//NL', $recieved);
        $_ENV['ORG_NAME'] = $_orgName;
    }
}
