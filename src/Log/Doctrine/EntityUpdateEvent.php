<?php

namespace App\Log\Doctrine;

use App\Log\AbstractEvent;
use App\Reflection\ClassNameService;

class EntityUpdateEvent extends AbstractEvent
{
    private $old;

    private $new;

    public function __construct($entity, $oldObject, $newValues)
    {
        $this->setEntity($entity);

        $this->old = $oldObject;
        $this->new = $newValues;
    }

    public function getOld()
    {
        return $this->old;
    }

    public function getNew()
    {
        $new = $this->old;
        foreach ($this->new as $field => $value) {
            $new[$field] = $value;
        }

        return $new;
    }

    public function getOldChanged()
    {
        $old = [];
        foreach ($this->new as $field => $value) {
            $old[$field] = $this->old[$field];
        }

        return $old;
    }

    public function getNewChanged()
    {
        return $this->new;
    }

    public function getTitle()
    {
        return 'Updated '.ClassNameService::fqcnToName($this->getEntityType());
    }
}
