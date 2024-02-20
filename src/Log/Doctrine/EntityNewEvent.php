<?php

namespace App\Log\Doctrine;

use App\Log\AbstractEvent;
use App\Reflection\ClassNameService;

class EntityNewEvent extends AbstractEvent
{
    private mixed $fields;

    /**
     * @param array<string, array{entity: string, identifier: mixed}|mixed|null> $fields
     */
    public function __construct(object $entity, $fields)
    {
        $this->setEntity($entity);

        $this->fields = $fields;
    }

    public function getFields(): mixed
    {
        return $this->fields;
    }

    public function getTitle(): string
    {
        $entityType = $this->getEntityType();
        assert(is_string($entityType));

        return 'Updated '.ClassNameService::fqcnToName($entityType);
    }
}
