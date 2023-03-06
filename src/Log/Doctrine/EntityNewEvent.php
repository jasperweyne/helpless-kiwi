<?php

namespace App\Log\Doctrine;

use App\Log\AbstractEvent;
use App\Reflection\ClassNameService;

class EntityNewEvent extends AbstractEvent
{
    /**
     * @var mixed
     */
    private $fields;

    /**
     * @param mixed $fields
     */
    public function __construct(object $entity, $fields)
    {
        $this->setEntity($entity);

        $this->fields = $fields;
    }

    /**
     * @return mixed
     */
    public function getFields()
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
