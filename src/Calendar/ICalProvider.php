<?php

namespace App\Calendar;

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
        $orgName = getenv('ORG_NAME') ? $_ENV['ORG_NAME'] : 'kiwi';

        $icalFactory = new \Welp\IcalBundle\Factory\Factory();

        $calendar = $icalFactory->createCalendar();
        $calendar
            ->setProdId('-//Helpless Kiwi//'.$orgName.' v1.0//NL')
            ->setTimezone(new \DateTimeZone(date_default_timezone_get()))
        ;

        foreach ($activities as $activity) {
            try {
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
            } catch (Error $error) {
                throw new Exception('Error: The creation of an activity failed.');
            }
            $calendar->addEvent($event);
        }

        return $calendar;
    }
}
