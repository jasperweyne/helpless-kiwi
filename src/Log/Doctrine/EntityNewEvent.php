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

    private $type;

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
        return 'Updated '.ClassNameService::fqcnToName($this->getEntityType());
    }
}
