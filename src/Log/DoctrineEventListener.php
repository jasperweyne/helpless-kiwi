<?php

namespace App\Log;

use App\Log\EntityNewEvent;
use App\Log\LoggableEntityInterface;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;

class DoctrineEventListener
{
    private $eventService;

    public function __construct(EventService $eventService)
    {
        $this->eventService = $eventService;
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        if (!$entity instanceof LoggableEntityInterface) {
            return;
        }

        $eventEntity = $this->eventService->hydrate(new EntityNewEvent($entity));

        $em = $args->getObjectManager();
        $em->persist($eventEntity);
        $em->flush();
    }
}
