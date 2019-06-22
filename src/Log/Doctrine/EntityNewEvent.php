<?php

namespace App\Log\Doctrine;

use App\Log\AbstractEvent;

class EntityNewEvent extends AbstractEvent
{
    public function __construct($entity)
    {
        $this->setEntity($entity);
    }
}
