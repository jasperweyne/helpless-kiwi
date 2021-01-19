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

    protected $now;
    protected $summary;
    protected $description;
    protected $location;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->iCalProvider = new ICalProvider();
        $this->firstActivity = new Activity();
        $this->secondActivity = new Activity();

        $this->now = new \DateTime();
        $this->summary = 'kiwi test summary';
        $this->description = 'kiwi test description';
        $this->location = new Location();

        $this->location->setAddress('@localhost');
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
        $this
            ->firstActivity
            ->setStart($this->now)
            ->setEnd($this->now)
            ->setName($this->summary)
            ->setLocation($this->location)
            ->setDescription($this->description)
        ;

        $recieved = $this->iCalProvider->icalSingle(
            $this->firstActivity
        )->export();
        $this->assertStringContainsString('PRODID:-//Helpless Kiwi//'.$_ENV['ORG_NAME'].' v1.0//NL', $recieved);
        $this->assertStringContainsString('DTSTART:'.$this->firstActivity->getStart()->format('Ymd\THis'), $recieved);
        $this->assertStringContainsString('SUMMARY:'.$this->summary, $recieved);
        $this->assertStringContainsString('LOCATION:'.$this->location->getAddress(), $recieved);
        $this->assertStringContainsString('DESCRIPTION:kiwi test description', $recieved);
    }

    /**
     * @test
     */
    public function icalsingleSuccesWithErrorHandling(): void
    {
        $this
            ->firstActivity
            ->setStart($this->now)
            ->setEnd($this->now)
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
        $this
            ->firstActivity
            ->setStart($this->now)
            ->setEnd($this->now)
            ->setName($this->summary)
            ->setLocation($this->location)
            ->setDescription($this->description)
        ;

        $recieved = $this->iCalProvider->icalFeed([
            $this->firstActivity,
        ])->export();
        $this->assertStringContainsString('PRODID:-//Helpless Kiwi//'.$_ENV['ORG_NAME'].' v1.0//NL', $recieved);
        $this->assertStringContainsString('DTSTART:'.$this->firstActivity->getStart()->format('Ymd\THis'), $recieved);
        $this->assertStringContainsString('SUMMARY:'.$this->summary, $recieved);
        $this->assertStringContainsString('LOCATION:'.$this->location->getAddress(), $recieved);
        $this->assertStringContainsString('DESCRIPTION:'.$this->description, $recieved);
    }

    /**
     * @test
     */
    public function icalfeedSuccesWithTwoValidEvents(): void
    {
        $this
            ->firstActivity
            ->setStart($this->now)
            ->setEnd($this->now)
            ->setName($this->summary)
            ->setLocation($this->location)
            ->setDescription($this->description)
        ;

        $this
            ->secondActivity
            ->setStart($this->now)
            ->setEnd($this->now)
            ->setName('second '.$this->summary)
            ->setLocation($this->location)
            ->setDescription('second '.$this->description)
        ;

        $recieved = $this->iCalProvider->icalFeed([
            $this->firstActivity,
            $this->secondActivity,
        ])->export();
        $this->assertStringContainsString('SUMMARY:'.$this->summary, $recieved);
        $this->assertStringContainsString('SUMMARY:second '.$this->summary, $recieved);
    }

    /**
     * @test
     */
    public function icalfeedSuccesWithOneValidAndOneInvalidEvent(): void
    {
        $this
            ->firstActivity
            ->setName($this->summary)
            ->setLocation($this->location)
            ->setDescription($this->description)
        ;

        $this
            ->secondActivity
            ->setStart($this->now)
            ->setEnd($this->now)
            ->setName('second '.$this->summary)
            ->setLocation($this->location)
            ->setDescription('second '.$this->description)
        ;

        $recieved = $this->iCalProvider->icalFeed([
            $this->firstActivity,
            $this->secondActivity,
        ])->export();
        $this->assertStringContainsString('SUMMARY:second '.$this->summary, $recieved);
    }

    /**
     * @test
     */
    public function icalfeedSuccesWithMissingEnvVariable(): void
    {
        $_orgName = $_ENV['ORG_NAME'];
        unset($_ENV['ORG_NAME']);

        $this
            ->firstActivity
            ->setStart($this->now)
            ->setEnd($this->now)
            ->setName($this->summary)
            ->setLocation($this->location)
            ->setDescription($this->description)
        ;

        $recieved = $this->iCalProvider->icalFeed([
            $this->firstActivity,
        ])->export();
        $this->assertStringContainsString('PRODID:-//Helpless Kiwi//kiwi v1.0//NL', $recieved);
        $_ENV['ORG_NAME'] = $_orgName;
    }
}
