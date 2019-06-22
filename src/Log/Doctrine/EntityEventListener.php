<?php

namespace App\Log\Doctrine;

use App\Log\Doctrine\EntityNewEvent;
use App\Log\EventService;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use App\Entity\Log\Event;

class EntityEventListener
{
    private $eventService;

    public function __construct(EventService $eventService)
    {
        $this->eventService = $eventService;
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        if ($entity instanceof Event) {
            return;
        }

        $this->eventService->log(new EntityNewEvent($entity));
    }
}
