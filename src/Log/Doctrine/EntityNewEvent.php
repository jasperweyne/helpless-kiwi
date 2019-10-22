<?php

namespace App\Log\Doctrine;

use App\Log\AbstractEvent;

class EntityNewEvent extends AbstractEvent
{
    private $fields;

    private $type;

    public function __construct($entity, $fields)
    {
        $this->setEntity($entity);

        $this->fields = $fields;
    }

    public function getFields()
    {
        return $this->fields;
    }

    public function getTitle()
    {
        return 'Added '.$this->getEntityType();
    }
}
