<?php

namespace App\Calendar;

use App\Entity\Activity\Activity;

class ICalProvider
{
    /*
     * create an ical file for a single event.
     */
    public function SingleEventIcal(
        Activity $activity
    )
    {
        $icalFactory = new \Welp\IcalBundle\Factory\Factory;
        $calendar = $icalFactory->createCalendar();
        $calendar->setProdId('Helpless Kiwi');

        $location = $icalFactory->createLocation();
        $location->setName($activity->getLocation()->getAddress());

        $organiser = $icalFactory->createOrganizer();
        $organiser
            ->setSentBy($_ENV['DEFAULT_FROM'])
            ->setValue($_ENV['DEFAULT_FROM'])
            ->setName(($activity->getAuthor() ? $activity->getAuthor()->getName() . ' - ' : '') . $_ENV['ORG_NAME'] ?? 'Kiwi')
        ;
        
        $event = $icalFactory->createCalendarEvent();
        $event->setStatus("CONFIRMED")
              ->setStart($activity->getStart())
              ->setEnd($activity->getEnd())
              ->setSummary($activity->getName())
              ->setDescription($activity->getDescription())
              ->setUid($activity->getId())
              ->setLocations([$location])
              ->setOrganizer($organiser)
        ;
        $calendar->addEvent($event);
        return $calendar;;
    }
}
