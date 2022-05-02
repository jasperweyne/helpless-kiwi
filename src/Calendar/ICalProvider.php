<?php

namespace App\Calendar;

use App\Entity\Activity\Activity;
use Error;
use Jsvrcek\ICS\Exception\CalendarEventException;
use Jsvrcek\ICS\Model\CalendarEvent;
use Welp\IcalBundle\Component\Calendar;

class ICalProvider
{
    /**
     * create an ical feed the passed activity array.
     */
    public function icalFeed(
        array $activities
    ): Calendar {
        $icalFactory = new \Welp\IcalBundle\Factory\Factory();
        $calendar = $this->createCalendar($icalFactory);

        foreach ($activities as $activity) {
            try {
                $calendar->addEvent($this->createEvent($activity, $icalFactory));
            } catch (Error $error) {
                continue;
            }
        }

        return $calendar;
    }

    /**
     * create an ical for passed activity.
     */
    public function icalSingle(
        Activity $activity
    ): Calendar {
        $icalFactory = new \Welp\IcalBundle\Factory\Factory();
        $calendar = $this->createCalendar($icalFactory);

        try {
            $calendar->addEvent($this->createEvent($activity, $icalFactory));
        } catch (Error $error) {
            throw new CalendarEventException('Error: Failed to create the event');
        }

        return $calendar;
    }

    private function createCalendar(
        \Welp\IcalBundle\Factory\Factory $icalFactory
    ): Calendar {
        $calendar = $icalFactory->createCalendar();
        $calendar
            ->setProdId('-//Helpless Kiwi//'.($_ENV['ORG_NAME'] ?? 'kiwi').' v1.0//NL')
            ->setTimezone(new \DateTimeZone(date_default_timezone_get()))
        ;

        return $calendar;
    }

    private function createEvent(
        Activity $activity,
        \Welp\IcalBundle\Factory\Factory $icalFactory
    ): CalendarEvent {
        $location = $icalFactory->createLocation();
        $location
            ->setName($activity->getLocation()->getAddress())
        ;

        $organiser = $icalFactory->createOrganizer();
        $organiser
            ->setSentBy($_ENV['DEFAULT_FROM'])
            ->setValue($_ENV['DEFAULT_FROM'])
            ->setName(($activity->getAuthor() ? $activity->getAuthor()->getName().' - ' : '').($_ENV['ORG_NAME'] ?? 'kiwi'));

        $event = $icalFactory->createCalendarEvent();
        $event
            ->setStatus('CONFIRMED')
            ->setStart($activity->getStart())
            ->setEnd($activity->getEnd())
            ->setSummary($activity->getName())
            ->setDescription($activity->getDescription())
            ->setUid($activity->getId())
            ->setLocations([$location])
            ->setOrganizer($organiser)
        ;

        return $event;
    }
}
