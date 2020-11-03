<?php

namespace App\Calendar;

use DateTime;

class ICalProvider
{
    /*
     * create an ical file for a single event.
     */
    public function SingleEventIcal(
        $attendeeName,
        $activity,
        DateTime $beginTime,
        DateTime $endTime
    )
    {
        $icalFactory = new \Welp\IcalBundle\Factory\Factory;
        $calendar = $icalFactory->createCalendar();
        $event = $icalFactory->createCalendarEvent();
        $event->setStart($beginTime)
              ->setEnd($endTime)
              ->setSummary($activity)
              ->setUid($attendeeName."-".$activity);
        $calendar->addEvent($event);
        return $calendar;;
    }
}
