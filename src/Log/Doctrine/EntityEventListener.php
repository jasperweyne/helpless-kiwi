<?php

namespace App\Log\Doctrine;

use App\Entity\Log\Event;
use App\Log\EventService;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\PersistentCollection;

class EntityEventListener
{
    /**
     * @var EventService
     */
    private $eventService;

    public function __construct(EventService $eventService)
    {
        $this->eventService = $eventService;
    }

    public function onFlush(OnFlushEventArgs $eventArgs): void
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
            if ($entity instanceof Event) {
                continue;
            }

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

    /**
     * @return array<string, array{entity: string, identifier: mixed}|mixed|null>
     */
    public function extractFields(object $entity, ClassMetadata $metadata)
    {
        /** @var \ReflectionProperty[] */
        $properties = $metadata->getReflectionProperties();

        $fields = [];
        foreach ($properties as $field => $property) {
            if (!$property->getValue($entity) instanceof PersistentCollection) {
                $fields[$field] = $this->sanitize($property->getValue($entity), $field, $metadata);
            }
        }

        return $fields;
    }

    /**
     * @param string        $field
     * @param ClassMetadata $metadata
     *
     * @return array{entity: string, identifier: mixed}|mixed|null
     */
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
