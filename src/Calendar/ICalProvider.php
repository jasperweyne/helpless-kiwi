<?php

namespace App\Calendar;

use App\Entity\Activity\Activity;
use Error;
use Exception;

class ICalProvider
{
    /**
     * create an ical feed the passed activity array.
     */
    public function icalFeed(
        array $activities
    ) {
        $calendar = $this->createCalendar();

        foreach ($activities as $activity) {
            try {
                $calendar->addEvent($this->createEvent($activity));
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
    ) {
        $calendar = $this->createCalendar();

        try {
            $calendar->addEvent($this->createEvent($activity));
        } catch (Error $error) {
            throw new Exception('Error: Failed to create the event');
        }

        return $calendar;
    }

    private function createCalendar()
    {
        $icalFactory = new \Welp\IcalBundle\Factory\Factory();

        $orgName = $_ENV['ORG_NAME'] ? $_ENV['ORG_NAME'] : 'kiwi';

        $calendar = $icalFactory->createCalendar();
        $calendar
            ->setProdId('-//Helpless Kiwi//'.$orgName.' v1.0//NL')
            ->setTimezone(new \DateTimeZone(date_default_timezone_get()))
        ;

        return $calendar;
    }

    private function createEvent(
        Activity $activity
    ) {
        $icalFactory = new \Welp\IcalBundle\Factory\Factory();
        $location = $icalFactory->createLocation();
        $location
            ->setName($activity->getLocation()->getAddress())
        ;

        $organiser = $icalFactory->createOrganizer();
        $organiser
            ->setSentBy($_ENV['DEFAULT_FROM'])
            ->setValue($_ENV['DEFAULT_FROM'])
            ->setName(($activity->getAuthor() ? $activity->getAuthor()->getName().' - ' : '').$_ENV['ORG_NAME'] ?? 'Kiwi')
        ;

        $event = $icalFactory->createCalendarEvent();
        $event
            ->setStatus('CONFIRMED')//is it?
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
