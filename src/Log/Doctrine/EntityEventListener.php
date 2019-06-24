<?php

namespace App\Log\Doctrine;

use App\Entity\Log\Event;
use App\Log\EventService;
use App\Log\ReflectionService;
use App\Log\Doctrine\EntityNewEvent;
use Doctrine\ORM\Event\OnFlushEventArgs;

class EntityEventListener
{
    private $eventService;

    private $reflService;

    public function __construct(EventService $eventService, ReflectionService $reflService)
    {
        $this->eventService = $eventService;
        $this->reflService  = $reflService;
    }

    public function onFlush(OnFlushEventArgs $eventArgs)
    {
        $em = $eventArgs->getEntityManager();
        $uow = $em->getUnitOfWork();

        // On create entity
        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            if ($entity instanceof Event) {
                continue;
            }
            
            $fields = $this->extractFields($entity, $em->getClassMetadata(get_class($entity)));

            $logEntity = $this->eventService->hydrate(new EntityNewEvent($entity, $fields));
            $logMeta = $em->getClassMetadata(get_class($logEntity));

            $em->persist($logEntity);
            $uow->computeChangeSet($logMeta, $logEntity);
        }

        // On edit entity
        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            $metadata = $em->getClassMetadata(get_class($entity));
            $original = $this->extractFields($entity, $metadata);
    
            $newFields = array();
            foreach ($uow->getEntityChangeSet($entity) as $field => $values) {
                $original[$field] = $this->sanitize($values[0], $field, $metadata);
                $newFields[$field] = $this->sanitize($values[1], $field, $metadata);
            }
    
            $logEntity = $this->eventService->hydrate(new EntityUpdateEvent($entity, $original, $newFields));
            $logMeta = $em->getClassMetadata(get_class($logEntity));

            $em->persist($logEntity);
            $uow->computeChangeSet($logMeta, $logEntity);
        }

        // On remove entity
        foreach ($uow->getScheduledEntityDeletions() as $entity) {

        }
    }

    public function extractFields($entity, $metadata) {
        $entityFqcn = $this->eventService->getClassName($entity);
        $properties = $this->reflService->getAllProperties($entityFqcn);

        $fields = array();
        foreach ($properties as $field => $property) {
            $fields[$field] = $this->sanitize($property->getValue($entity), $field, $metadata);
        }

        return $fields;
    }

    private function sanitize($value, $field, $metadata) {
        if (array_key_exists($field, $metadata->getAssociationMappings())) {
            return null; // todo: support references
        }

        return $value;
    }
}
