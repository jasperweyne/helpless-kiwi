<?php

namespace App\Log\Doctrine;

use App\Log\AbstractEvent;
use App\Reflection\ClassNameService;

class EntityUpdateEvent extends AbstractEvent
{
    /**
     * @var array<string, mixed>
     */
    private $old;

    /**
     * @var array<string, mixed>
     */
    private $new;

    /**
     * @param array<string, mixed> $oldObject
     * @param array<string, mixed> $newValues
     */
    public function __construct(object $entity, array $oldObject, array $newValues)
    {
        $this->setEntity($entity);

        $this->old = $oldObject;
        $this->new = $newValues;
    }

    /**
     * @return array<string, mixed>
     */
    public function getOld(): array
    {
        return $this->old;
    }

    /**
     * @return array<string, mixed>
     */
    public function getNew(): array
    {
        $new = $this->old;
        foreach ($this->new as $field => $value) {
            $new[$field] = $value;
        }

        return $new;
    }

    /**
     * @return array<string, mixed>
     */
    public function getOldChanged(): array
    {
        $old = [];
        foreach ($this->new as $field => $value) {
            $old[$field] = $this->old[$field];
        }

        return $old;
    }

    /**
     * @return array<string, mixed>
     */
    public function getNewChanged(): array
    {
        return $this->new;
    }

    public function getTitle(): string
    {
        return 'Updated '.ClassNameService::fqcnToName($this->getEntityType());
    }
}
