<?php

namespace App\Calendar;

use App\Entity\Activity\Activity;

class ICalProvider
{
    /*
     * create an ical file for a single event.
     */
    public function singleEventIcal(
        Activity $activity
    )
    {
        $icalFactory = new \Welp\IcalBundle\Factory\Factory;
        $calendar = $icalFactory->createCalendar()
            ->setProdId('Helpless Kiwi');

        $location = $icalFactory->createLocation()
            ->setName($activity->getLocation()->getAddress());

        $organiser = $icalFactory->createOrganizer();
        $organiser
            ->setSentBy($_ENV['DEFAULT_FROM'])
            ->setValue($_ENV['DEFAULT_FROM'])
            ->setName(($activity->getAuthor() ? $activity->getAuthor()->getName() . ' - ' : '') . $_ENV['ORG_NAME'] ?? 'Kiwi')
        ;

        $event = $icalFactory->createCalendarEvent()
            ->setStatus("CONFIRMED")
            ->setStart($activity->getStart())
            ->setEnd($activity->getEnd())
            ->setSummary($activity->getName())
            ->setDescription($activity->getDescription())
            ->setUid($activity->getId())
            ->setLocations([$location])
            ->setOrganizer($organiser)
        ;
        $calendar->addEvent($event);
        return $calendar;
    }

    /*
     * create an ical feed for all passed activities
     */
    public function icalFeed(
        array $activities
    )
    {
        $icalFactory = new \Welp\IcalBundle\Factory\Factory;
        $calendar = $icalFactory->createCalendar()
            ->setProdId('Helpless Kiwi');
        foreach($activities as $activity) {
            //if typeof?
            $location = $icalFactory->createLocation()
                ->setName($activity->getLocation()->getAddress());

            $organiser = $icalFactory->createOrganizer();
            $organiser
                ->setSentBy($_ENV['DEFAULT_FROM'])
                ->setValue($_ENV['DEFAULT_FROM'])
                ->setName(($activity->getAuthor() ? $activity->getAuthor()->getName() . ' - ' : '') . $_ENV['ORG_NAME'] ?? 'Kiwi')
            ;

            $event = $icalFactory->createCalendarEvent()
                ->setStatus("CONFIRMED")//is it?
                ->setStart($activity->getStart())
                ->setEnd($activity->getEnd())
                ->setSummary($activity->getName())
                ->setDescription($activity->getDescription())
                ->setUid($activity->getId())
                ->setLocations([$location])
                ->setOrganizer($organiser)
            ;
            $calendar->addEvent($event);
        }
        return $calendar;
    }
}
