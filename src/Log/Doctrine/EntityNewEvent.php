<?php

namespace App\Log\Doctrine;

use App\Log\AbstractEvent;
use App\Reflection\ClassNameService;

class EntityNewEvent extends AbstractEvent
{
    /**
     * @var array<string, mixed>
     */
    private $fields;

    private $type;

    /**
     * @param array<string, mixed> $fields
     */
    public function __construct(object $entity, array $fields)
    {
        $this->setEntity($entity);

        $this->fields = $fields;
    }

    /**
     * @return array<string, mixed>
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    public function getTitle(): string
    {
        return 'Updated '.ClassNameService::fqcnToName($this->getEntityType());
    }
}
