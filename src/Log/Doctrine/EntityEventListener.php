<?php

namespace App\Log\Doctrine;

use App\Entity\Log\Event;
use App\Log\EventService;
use App\Reflection\ReflectionService;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\PersistentCollection;

class EntityEventListener
{
    private $eventService;

    private $reflService;

    public function __construct(EventService $eventService, ReflectionService $reflService)
    {
        $this->eventService = $eventService;
        $this->reflService = $reflService;
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

            $metadata = $em->getClassMetadata(get_class($entity));
            $fields = $this->extractFields($entity, $metadata);

            $logEntity = $this->eventService->hydrate(new EntityNewEvent($entity, $fields));
            if (null == $logEntity) {
                continue;
            }

            $logMeta = $em->getClassMetadata(get_class($logEntity));
            $em->persist($logEntity);
            $uow->computeChangeSet($logMeta, $logEntity);
        }

        // On edit entity
        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            $metadata = $em->getClassMetadata(get_class($entity));
            $original = $this->extractFields($entity, $metadata);

            $newFields = [];
            foreach ($uow->getEntityChangeSet($entity) as $field => $values) {
                if (!$values[0] instanceof PersistentCollection) {
                    $original[$field] = $this->sanitize($values[0], $field, $metadata);
                    $newFields[$field] = $this->sanitize($values[1], $field, $metadata);
                }
            }

            $logEntity = $this->eventService->hydrate(new EntityUpdateEvent($entity, $original, $newFields));
            if (null == $logEntity) {
                continue;
            }

            $logMeta = $em->getClassMetadata(get_class($logEntity));
            $em->persist($logEntity);
            $uow->computeChangeSet($logMeta, $logEntity);
        }

        // On remove entity
        foreach ($uow->getScheduledEntityDeletions() as $entity) {
        }
    }

    public function extractFields($entity, $metadata)
    {
        $properties = $metadata->getReflectionProperties();

        $fields = [];
        foreach ($properties as $field => $property) {
            if (!$property->getValue($entity) instanceof PersistentCollection) {
                $fields[$field] = $this->sanitize($property->getValue($entity), $field, $metadata);
            }
        }

        return $fields;
    }

    private function sanitize($value, $field, $metadata)
    {
        if (null === $value) {
            return null;
        }

        if (array_key_exists($field, $metadata->getAssociationMappings())) {
            if ($value instanceof PersistentCollection) {
                // Is initialized by Doctrine through other entities; skip
                throw new \LogicException('Values for this field should not be sanitized; skip this field');
            } else {
                return [
                    'entity' => $this->eventService->getClassName($value),
                    'identifier' => $this->eventService->getIdentifier($value),
                ];
            }
        }

        return $value;
    }
}
