<?php

namespace App\Calendar;

use App\Entity\Activity\Activity;
use Eluceo\iCal\Domain\Entity\Calendar;
use Eluceo\iCal\Domain\Entity\Event;
use Eluceo\iCal\Domain\Entity\TimeZone;
use Eluceo\iCal\Domain\Enum\EventStatus;
use Eluceo\iCal\Domain\ValueObject\DateTime;
use Eluceo\iCal\Domain\ValueObject\EmailAddress;
use Eluceo\iCal\Domain\ValueObject\Location;
use Eluceo\iCal\Domain\ValueObject\Organizer;
use Eluceo\iCal\Domain\ValueObject\TimeSpan;
use Eluceo\iCal\Domain\ValueObject\UniqueIdentifier;
use Eluceo\iCal\Presentation\Factory\CalendarFactory;

class ICalProvider
{
    /**
     * create an ical feed the passed activity array.
     *
     * @param Activity[] $activities
     */
    public function icalFeed(array $activities): string
    {
        $calendar = $this->createCalendar();

        foreach ($activities as $activity) {
            try {
                $calendar->addEvent($this->createEvent($activity));
            } catch (\Error) {
                continue;
            }
        }

        $factory = new CalendarFactory();
        return strval($factory->createCalendar($calendar));
    }

    /**
     * create an ical for passed activity.
     */
    public function icalSingle(Activity $activity): string
    {
        $calendar = $this->createCalendar();
        $calendar->addEvent($this->createEvent($activity));

        $factory = new CalendarFactory();
        return strval($factory->createCalendar($calendar));
    }

    private function createCalendar(): Calendar
    {
        $calendar = new Calendar();
        return $calendar
            ->setProductIdentifier('-//Helpless Kiwi//' . ($_ENV['ORG_NAME'] ?? 'kiwi') . ' v1.0//NL')
            ->addTimeZone(new TimeZone(date_default_timezone_get()));
    }

    private function createEvent(Activity $activity): Event
    {
        $address = $activity->getLocation()?->getAddress();
        assert($address !== null);
        $location = new Location($address);

        $organiser = new Organizer(
            new EmailAddress($_ENV['DEFAULT_FROM']),
            ($activity->getAuthor() !== null ? $activity->getAuthor()->getName() . ' - ' : '') . ($_ENV['ORG_NAME'] ?? 'kiwi')
        );

        assert($activity->getStart() !== null);
        assert($activity->getEnd() !== null);
        $timespan = new TimeSpan(
            new DateTime($activity->getStart(), false),
            new DateTime($activity->getEnd(), false)
        );

        $activityId = $activity->getId();
        assert($activityId !== null);
        $event = new Event(new UniqueIdentifier($activityId));

        $activityName = $activity->getName();
        assert($activityName !== null);
        return $event
            ->setStatus(EventStatus::CONFIRMED())
            ->setOccurrence($timespan)
            ->setSummary($activityName)
            ->setDescription($activity->getDescription() ?? '')
            ->setLocation($location)
            ->setOrganizer($organiser);
    }
}
