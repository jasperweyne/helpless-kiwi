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
            } catch (\Error $_) {
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
        $calendar
            ->setProductIdentifier('-//Helpless Kiwi//'.($_ENV['ORG_NAME'] ?? 'kiwi').' v1.0//NL')
            ->addTimeZone(new TimeZone(date_default_timezone_get()))
        ;

        return $calendar;
    }

    private function createEvent(Activity $activity): Event
    {
        $location = new Location($activity->getLocation()->getAddress());

        $organiser = new Organizer(
            new EmailAddress($_ENV['DEFAULT_FROM']),
            ($activity->getAuthor() ? $activity->getAuthor()->getName().' - ' : '').($_ENV['ORG_NAME'] ?? 'kiwi')
        );

        $timespan = new TimeSpan(new DateTime($activity->getStart(), false), new DateTime($activity->getEnd(), false));

        $event = new Event(new UniqueIdentifier($activity->getId()));
        $event
            ->setStatus(EventStatus::CONFIRMED())
            ->setOccurrence($timespan)
            ->setSummary($activity->getName())
            ->setDescription($activity->getDescription())
            ->setLocation($location)
            ->setOrganizer($organiser)
        ;

        return $event;
    }
}
